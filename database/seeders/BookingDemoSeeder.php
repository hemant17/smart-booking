<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\WorkingRule;

class BookingDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 30
        ]);

        Service::create([
            'name' => 'Personal Training',
            'duration_minutes' => 60
        ]);

        WorkingRule::create([
            'type' => 'weekly',
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_interval' => 30,
            'active' => true
        ]);

        WorkingRule::create([
            'type' => 'weekly',
            'weekday' => 2,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_interval' => 30,
            'active' => true
        ]);
    }
}
