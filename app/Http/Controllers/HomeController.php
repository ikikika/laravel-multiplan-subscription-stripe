<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
         // Get all plans from stripe api
         $stripe = new \Stripe\Stripe();
         $stripe->setApiKey(env('STRIPE_SECRET'));
         $plans = \Stripe\Plan::all()->data;

         /*$user = DB::table('users')
                  ->join('subscriptions', 'users.id', '=', 'subscriptions.user_id')
                  ->where('users.id', '=', Auth::user()->id)
                  ->get();*/


        $subscribed_plans = DB::table('subscriptions')
                            ->where('user_id', '=', Auth::user()->id)
                            ->where(function($query)
                              {
                                $mytime = Carbon\Carbon::now();
                                $query->where('ends_at', '=', NULL)
                                      ->orWhere('ends_at', '>', $mytime);
                              })
                            ->get();

         // Check is subscribed
         //$is_subscribed = Auth::user()->subscribed('main');

         // If subscribed get the subscription
         //$subscription = Auth::user()->subscription('main');

         //return view('home', compact('plans', 'is_subscribed', 'subscription'));





         $subscribed_plan_names = [];
         foreach( $subscribed_plans as $plan ){
           array_push( $subscribed_plan_names, $plan->stripe_plan );
         }
        //dd($subscribed_plan_names);

         return view('home')->with('allplans', $plans)->with('subscribed_plans', $subscribed_plans)->with('subscribed_plan_names', $subscribed_plan_names);



     }
}
