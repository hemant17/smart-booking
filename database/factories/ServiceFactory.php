<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Haircut', 'Consultation', 'Massage', 'Personal Training']),
            'duration_minutes' => 30,
        ];
    }
}