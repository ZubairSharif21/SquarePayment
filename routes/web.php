<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GooglePaymentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

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


Route::post('pay-card', [PaymentController::class, 'pay_card'])->name('pay');


Route::get('/card/payment', [PaymentController::class, 'showPaymentForm'])->name('card.payment.form');
Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');
Route::view('/google','GooglePay' );







Route::post('/process-google-pay', [GoogleController::class, 'processGooglePay'])->name('process-google-pay');

