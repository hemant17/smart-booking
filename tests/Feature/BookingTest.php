<?php

use App\Models\Service;
use App\Models\WorkingRule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('availability returns correct slots based on working rules and service duration', function () {
    $service = Service::factory()->create(['duration_minutes' => 30]);

    $nextMonday = Carbon::now()->next('monday')->toDateString();

    WorkingRule::factory()->create([
        'type' => 'weekly',
        'weekday' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '10:00',
        'slot_interval' => 30,
        'active' => true,
    ]);

    $response = $this->getJson("/api/availability?date={$nextMonday}&service_id={$service->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment([
            'start' => Carbon::parse($nextMonday)->setTime(9, 0)->toDateTimeString(),
            'end' => Carbon::parse($nextMonday)->setTime(9, 30)->toDateTimeString(),
        ])
        ->assertJsonFragment([
            'start' => Carbon::parse($nextMonday)->setTime(9, 30)->toDateTimeString(),
            'end' => Carbon::parse($nextMonday)->setTime(10, 0)->toDateTimeString(),
        ]);
});

test('availability excludes overlapping appointments', function () {
    $service = Service::factory()->create(['duration_minutes' => 30]);

    $nextMonday = Carbon::now()->next('monday');

    WorkingRule::factory()->create([
        'type' => 'weekly',
        'weekday' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '10:00',
        'slot_interval' => 30,
        'active' => true,
    ]);

    // Create an existing appointment that occupies the first slot
    Appointment::factory()->create([
        'service_id' => $service->id,
        'client_email' => 'existing@example.com',
        'start_at' => $nextMonday->copy()->setTime(9, 0),
        'end_at' => $nextMonday->copy()->setTime(9, 30),
    ]);

    $response = $this->getJson("/api/availability?date={$nextMonday->toDateString()}&service_id={$service->id}");

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'start' => $nextMonday->copy()->setTime(9, 30)->toDateTimeString(),
            'end' => $nextMonday->copy()->setTime(10, 0)->toDateTimeString(),
        ])
        ->assertJsonMissing([
            'start' => $nextMonday->copy()->setTime(9, 0)->toDateTimeString(),
        ]);
});

test('booking a free slot succeeds and creates appointment', function () {
    $service = Service::factory()->create(['duration_minutes' => 30]);

    $nextMonday = Carbon::now()->next('monday')->setTime(9, 0);

    WorkingRule::factory()->create([
        'type' => 'weekly',
        'weekday' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '10:00',
        'slot_interval' => 30,
        'active' => true,
    ]);

    $bookingData = [
        'service_id' => $service->id,
        'start_at' => $nextMonday->toDateTimeString(),
        'client_email' => 'client@example.com',
    ];

    $response = $this->postJson('/api/bookings', $bookingData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'service_id' => $service->id,
            'client_email' => 'client@example.com',
            'start_at' => $nextMonday->toDateTimeString(),
            'end_at' => $nextMonday->copy()->addMinutes(30)->toDateTimeString(),
        ]);

    $this->assertDatabaseHas('appointments', [
        'service_id' => $service->id,
        'client_email' => 'client@example.com',
        'start_at' => $nextMonday->toDateTimeString(),
    ]);
});

test('double booking prevention returns 409 conflict', function () {
    $service = Service::factory()->create(['duration_minutes' => 30]);

    $nextMonday = Carbon::now()->next('monday')->setTime(9, 0);

    WorkingRule::factory()->create([
        'type' => 'weekly',
        'weekday' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '10:00',
        'slot_interval' => 30,
        'active' => true,
    ]);

    // Create first booking
    Appointment::factory()->create([
        'service_id' => $service->id,
        'client_email' => 'first@example.com',
        'start_at' => $nextMonday,
        'end_at' => $nextMonday->copy()->addMinutes(30),
    ]);

    // Try to book the same slot again
    $bookingData = [
        'service_id' => $service->id,
        'start_at' => $nextMonday->toDateTimeString(),
        'client_email' => 'second@example.com',
    ];

    $response = $this->postJson('/api/bookings', $bookingData);

    $response->assertStatus(409)
        ->assertJson([
            'message' => 'Time slot is already booked'
        ]);

    // Ensure no new appointment was created
    $this->assertDatabaseCount('appointments', 1);
    $this->assertDatabaseMissing('appointments', [
        'client_email' => 'second@example.com',
    ]);
});

test('availability returns empty when no working rules exist', function () {
    $service = Service::factory()->create();

    $nextMonday = Carbon::now()->next('monday')->toDateString();

    $response = $this->getJson("/api/availability?date={$nextMonday}&service_id={$service->id}");

    $response->assertStatus(200)
        ->assertJsonCount(0);
});

test('availability excludes past times for current date', function () {
    $service = Service::factory()->create(['duration_minutes' => 30]);

    $today = Carbon::now()->setHours(9, 0, 0);

    WorkingRule::factory()->create([
        'type' => 'date',
        'date' => $today->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'slot_interval' => 30,
        'active' => true,
    ]);

    // Mock current time to be 9:30 AM
    Carbon::setTestNow($today->copy()->setTime(9, 30));

    $response = $this->getJson("/api/availability?date={$today->toDateString()}&service_id={$service->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment([
            'start' => $today->copy()->setTime(10, 0)->toDateTimeString(),
            'end' => $today->copy()->setTime(10, 30)->toDateTimeString(),
        ])
        ->assertJsonMissing([
            'start' => $today->copy()->setTime(8, 0)->toDateTimeString(),
        ])
        ->assertJsonMissing([
            'start' => $today->copy()->setTime(8, 30)->toDateTimeString(),
        ])
        ->assertJsonMissing([
            'start' => $today->copy()->setTime(9, 0)->toDateTimeString(),
        ]);

    Carbon::setTestNow(); // Reset test now
});

test('booking validation fails with invalid data', function () {
    $response = $this->postJson('/api/bookings', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['service_id', 'start_at', 'client_email']);
});

test('booking fails when start_time is in the past', function () {
    $service = Service::factory()->create();

    $pastTime = Carbon::now()->subHour();

    $bookingData = [
        'service_id' => $service->id,
        'start_at' => $pastTime->toDateTimeString(),
        'client_email' => 'client@example.com',
    ];

    $response = $this->postJson('/api/bookings', $bookingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start_at']);
});