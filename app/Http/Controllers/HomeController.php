<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                            ->get();

         // Check is subscribed
         //$is_subscribed = Auth::user()->subscribed('main');

         // If subscribed get the subscription
         //$subscription = Auth::user()->subscription('main');

         //return view('home', compact('plans', 'is_subscribed', 'subscription'));

         $subscribed = \Stripe\Customer::all()->data[0]->subscriptions->data[0]->items->data;

         //dd($subscribed);

         return view('home')->with('allplans', $plans)->with('subscribed_plans', $subscribed);



     }
}
