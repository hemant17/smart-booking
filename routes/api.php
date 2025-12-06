<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\WorkingRuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [AvailabilityController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/working-rules', [WorkingRuleController::class, 'store']);