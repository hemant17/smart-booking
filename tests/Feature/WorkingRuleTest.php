<?php

use App\Models\WorkingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a weekly working rule', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30,
        'active' => true
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'type' => 'weekly',
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_interval' => 30,
            'active' => true
        ]);

    $this->assertDatabaseHas('working_rules', [
        'type' => 'weekly',
        'weekday' => 1,
        'date' => null,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30,
        'active' => true
    ]);
});

test('admin can create a date-specific working rule', function () {
    $ruleData = [
        'type' => 'date',
        'date' => '2030-01-01',
        'start_time' => '10:00',
        'end_time' => '14:00',
        'slot_interval' => 60,
        'active' => true
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'type' => 'date',
            'date' => '2030-01-01',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'slot_interval' => 60,
            'active' => true
        ]);

    $this->assertDatabaseHas('working_rules', [
        'type' => 'date',
        'date' => '2030-01-01',
        'weekday' => null,
        'start_time' => '10:00',
        'end_time' => '14:00',
        'slot_interval' => 60,
        'active' => true
    ]);
});

test('validation fails when required fields are missing for weekly rule', function () {
    $ruleData = [
        'type' => 'weekly',
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['weekday']);

    $this->assertDatabaseMissing('working_rules', [
        'type' => 'weekly',
        'start_time' => '09:00',
    ]);
});

test('validation fails when required fields are missing for date rule', function () {
    $ruleData = [
        'type' => 'date',
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date']);

    $this->assertDatabaseMissing('working_rules', [
        'type' => 'date',
        'start_time' => '09:00',
    ]);
});

test('validation fails when end_time is before start_time', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '17:00',
        'end_time' => '09:00',
        'slot_interval' => 30,
        'active' => true
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_time']);

    $this->assertDatabaseMissing('working_rules', [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '17:00',
        'end_time' => '09:00',
    ]);
});

test('validation fails when type is invalid', function () {
    $ruleData = [
        'type' => 'invalid',
        'weekday' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('validation fails when slot_interval is less than 1', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 0
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slot_interval']);
});

test('weekly rule stores null in date field', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 3,
        'start_time' => '08:00',
        'end_time' => '16:00',
        'slot_interval' => 45,
        'active' => false
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('working_rules', [
        'type' => 'weekly',
        'weekday' => 3,
        'date' => null,
        'start_time' => '08:00',
        'end_time' => '16:00',
        'slot_interval' => 45,
        'active' => false
    ]);
});

test('date rule stores null in weekday field', function () {
    $ruleData = [
        'type' => 'date',
        'date' => '2030-12-25',
        'start_time' => '11:00',
        'end_time' => '15:00',
        'slot_interval' => 90,
        'active' => true
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('working_rules', [
        'type' => 'date',
        'date' => '2030-12-25',
        'weekday' => null,
        'start_time' => '11:00',
        'end_time' => '15:00',
        'slot_interval' => 90,
        'active' => true
    ]);
});

test('validation fails when weekday is outside 1-7 range for weekly rule', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 8,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['weekday']);
});

test('validation fails when time format is invalid', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '9am',
        'end_time' => '5pm',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start_time', 'end_time']);
});

test('active field defaults to true when not provided', function () {
    $ruleData = [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30
    ];

    $response = $this->postJson('/api/working-rules', $ruleData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'active' => true
        ]);

    $this->assertDatabaseHas('working_rules', [
        'type' => 'weekly',
        'weekday' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_interval' => 30,
        'active' => true
    ]);
});