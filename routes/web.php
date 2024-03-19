<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::match(
    ['GET', 'POST'],
    'payments/callback',
    [PaymentController::class, 'callback']
)
    ->name('payment.callback');


Route::post('payments/create', [PaymentController::class, 'create'])->name('payment.create');
Route::get('payments', [PaymentController::class, 'index'])->name('payment.index');
