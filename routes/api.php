<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [AvailabilityController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);