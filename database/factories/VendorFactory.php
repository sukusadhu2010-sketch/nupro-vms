<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            // user_id is optional — vendors are independent of User
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'particulars' => $this->faker->word(),
            'vendor_type' => $this->faker->randomElement(Vendor::VENDOR_TYPES),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended']),
            'gst_no' => $this->faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]'),
        ];
    }

    /**
     * State: vendor linked to a User (optional association).
     */
    public function withUser(): static
    {
        return $this->state(fn () => ['user_id' => \App\Models\User::factory()]);
    }
}

