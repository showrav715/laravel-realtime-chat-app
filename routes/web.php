<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
Route::get('/', function () { 
    return redirect()->route('dashboard');
})->name('welcome');

Auth::routes();

Route::get('/dashboard', [ApiController::class, 'index'])
    ->name('dashboard');
Route::get('/messages', [ApiController::class, 'messages'])
    ->name('messages');
Route::post('/message', [ApiController::class, 'message'])
    ->name('message');
Route::get("message/{user_id}", [ApiController::class, 'chat'])
    ->name('messages.chat');