<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Subscription;

class SubscribeController extends Controller
{
  public function __construct() {
  }

  public function payment(Request $request){

    $selected_plans = $request->input('plan');

    $cost = 0;
    foreach( $selected_plans as $selected_plan){
      $cost = $cost + $this->getPlanByIdOrFail($selected_plan)->amount;
    }

    return view('subscribe')->with( 'cost', $cost/100)->with( compact('selected_plans') )->with('user', Auth::user() );
  }

  private function getPlanByIdOrFail($id)
  {
      $stripe = new \Stripe\Stripe();
      $stripe->setApiKey(env('STRIPE_SECRET'));
      $plans = \Stripe\Plan::all()->data;

      if( ! $plans ) throw new NotFoundHttpException;

      return array_first(array_filter( $plans, function($plan) use ($id) {
          return $id == $plan->id;
      }));
  }

  public function subscribe(Request $request)
  {

    $email = Auth::user()->email;

    if( Auth::user()->stripe_id ){
      $customer_id = Auth::user()->stripe_id;
    } else {
      $customer_id = $this->registerUser($email, $request->input('stripeToken') );
    }

    if( $customer_id ){
      $selected_plans = $request->input('plan');

      $this->subscribePlan($customer_id, $selected_plans);
      return redirect()->route('home')->with('status', implode(",", $selected_plans).' plans subscribed.');
    }

  }

  public function registerUser($email, $stripeToken)
  {

    $customer = $this->registerUserOnStripe($email, $stripeToken);

    $user = user::find( Auth::user()->id );
    $user->stripe_id = $customer->id;
    $user->card_brand = $customer->sources->data[0]->brand;
    $user->card_last_four =$customer->sources->data[0]->last4;
    $user->save();

    if( $customer ){
      return $customer->id;
    } else {
      return fasle;
    }

  }

  // function to register user on stripe
  public function registerUserOnStripe($email, $stripeToken)
  {
    try {
      $stripe = new \Stripe\Stripe();
      $stripe->setApiKey(env('STRIPE_SECRET'));
      $customer = \Stripe\Customer::create(array(
        'email' => $email,
        'source' => $stripeToken
      ));

      if (!empty($customer) && !empty($customer["id"])) {
        return $customer;
      } else {
        return $this->response->responseServerError(Lang::get('User::messages.gateway_error'));
      }
    } catch (\Exception $e) {
      //Log::error("PaymentContractImpl::registerUserOnStripe() there is some exception " . $e->getMessage());
    return false;
    }
  }

  public function subscribePlan($customer_id, $selected_plans) {
    $stripe = new \Stripe\Stripe();
    $stripe->setApiKey(env('STRIPE_SECRET'));

    $plans = [];
    foreach( $selected_plans as $selected_plan){
      array_push($plans, array(
        "plan" => $selected_plan
      ));
    }

    $subscription = \Stripe\Subscription::create(array(
      "customer" => $customer_id,
      "items" => $plans
    ));

    $sub_items = $subscription->items->data;

    for( $i = 0; $i < count( $sub_items ); $i++ ){
      $sub = new Subscription;
      $sub->db_id = uniqid();
      $sub->user_id = Auth::user()->id;
      $sub->stripe_id = $subscription->id;
      $sub->stripe_plan = $sub_items[$i]->plan->id;
      $sub->sub_item_id = $sub_items[$i]->id;
      $sub->quantity = 1;
      $sub->save();
    }



  }

  public function cancelSubscription(Request $request){

    $sub_item_id = $request->input('sub_item_id');
    $sub_id = $request->input('sub_id');
    $db_id = $request->input('db_id');

    $stripe = new \Stripe\Stripe();
    $stripe->setApiKey(env('STRIPE_SECRET'));
    $subscription = \Stripe\Subscription::retrieve($sub_id);
    $end_date = date('Y-m-d H:i:s', $subscription->current_period_end);

    $subscribed_item_db = subscription::where('db_id',  $db_id )->first();
    $subscribed_item_db->sub_item_id = NULL;
    $subscribed_item_db->ends_at = $end_date;
    $subscribed_item_db->save();

    $plan_name = $subscribed_item_db->stripe_plan;

    if( count($subscription->items->data) == 1 ){

      $result = $subscription->cancel(['at_period_end' => true]);

    } else {

      $sub_item = \Stripe\SubscriptionItem::retrieve($sub_item_id);
      $result = $sub_item->delete(['prorate' => true]);
    }


    return redirect()->route('home')->with('status', 'plan '.$plan_name.' cancelled.');
  }

  public function resumeSubscription(Request $request)
  {
    $sub_id = $request->input('sub_id');
    $sub_item_id = $request->input('sub_item_id');
    $db_id = $request->input('db_id');

    $subscribed_item_db = subscription::where('db_id',  $db_id )->first();

    $stripe = new \Stripe\Stripe();
    $stripe->setApiKey(env('STRIPE_SECRET'));
    $subscription = \Stripe\Subscription::retrieve($sub_id);


    if( count($subscription->items->data) == 1 && $subscription->cancel_at_period_end ){

      $sub_plan_name = $request->input('plan_name');

      $subscription->plan = $sub_plan_name;
      $subscription->save();

      $subscribed_item_db->sub_item_id = end($subscription->items->data)->id;
      $subscribed_item_db->ends_at = NULL;
      $subscribed_item_db->save();


    } else if( ( count($subscription->items->data) == 1 && !$subscription->cancel_at_period_end ) || count($subscription->items->data) > 1 ) {

      $sub_items_arr = [];
      foreach( $subscription->items->data as $sub_item ){
        array_push($sub_items_arr, [
          'id' => $sub_item->id,
          'quantity' => $sub_item->quantity
        ]);
      }

      array_push($sub_items_arr, [
        'plan' => $subscribed_item_db->stripe_plan,
        'quantity' => '1'
      ]);

      $subscription->items = $sub_items_arr;
      $subscription->save();

      $subscribed_item_db->sub_item_id = end($subscription->items->data)->id;
      $subscribed_item_db->ends_at = NULL;
      $subscribed_item_db->save();


    }

    return redirect()->route('home')->with('status', 'plan '.$subscribed_item_db->stripe_plan.' resumed.');
  }



}
