<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{

  public function index()
  {
    try {
      $stripe = new \Stripe\Stripe();
      $stripe->setApiKey(env('STRIPE_SECRET'));
      $invoices = \Stripe\Invoice::all(array('customer' => Auth::user()->stripe_id ))->data;
    } catch ( \Exception $e ) {
        session()->flash('status', $e->getMessage());
    }

    //dd($invoices);
    return view('invoice', compact('invoices'));

  }

  public function show( $id ){
    $stripe = new \Stripe\Stripe();
    $stripe->setApiKey(env('STRIPE_SECRET'));
    $invoice = \Stripe\Invoice::retrieve($id);
    //dd($invoice);

    return view('invoice_detail', compact('invoice'));
  }

}
