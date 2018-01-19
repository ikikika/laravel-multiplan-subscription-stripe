<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();


Route::group(['middleware' => ['auth']], function(){

    Route::get('/home', 'HomeController@index')->name('home');
    //Route::get('/plan/{id}', 'PlanController@show')->name('plan');

    Route::group(['prefix' => 'subscribe'], function(){

        Route::post('/payment', 'SubscribeController@payment')->name('payment');
        Route::post('/', 'SubscribeController@subscribe')->name('subscribe');
        Route::post('/cancel', 'SubscribeController@cancelSubscription')->name('cancelSubscription');
        //Route::post('/cancel', 'PlanController@cancelSubscription')->name('subscriptionCancel');
        Route::post('/resume', 'SubscribeController@resumeSubscription')->name('subscriptionResume');

        //Route::get('/invoices', 'InvoiceController@index')->name('invoices');
        //Route::get('/invoice/{id}', 'InvoiceController@download')->name('downloadInvoice');

    });


});
