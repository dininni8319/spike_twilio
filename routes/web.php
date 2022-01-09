<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;

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

Route::get('/twilio/newroom', [TwilioController::class, 'newRoom'])->name('twilio.newroom');

Route::get('/twilio/joinroom', [TwilioController::class, 'joinRoom'])->name('twilio.joinroom');

Route::get('/twilio/room/{room_sid}', [TwilioController::class, 'closeRoom'])->name('twilio.closeroom');
