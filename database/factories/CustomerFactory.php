<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            // user_id is optional — customers are independent of User
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'gst_no' => $this->faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]'),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended']),
        ];
    }

    /**
     * State: customer linked to a User (optional association).
     */
    public function withUser(): static
    {
        return $this->state(fn () => ['user_id' => \App\Models\User::factory()]);
    }
}

