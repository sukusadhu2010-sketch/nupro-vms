<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'symbol' => 'pc', 'description' => 'Individual item or piece'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'description' => 'Weight in kilograms'],
            ['name' => 'Meter', 'symbol' => 'm', 'description' => 'Length in meters'],
            ['name' => 'Liter', 'symbol' => 'L', 'description' => 'Volume in liters'],
            ['name' => 'Box', 'symbol' => 'box', 'description' => 'Packaged in a box'],
            ['name' => 'Set', 'symbol' => 'set', 'description' => 'A set of items'],
            ['name' => 'Pair', 'symbol' => 'pair', 'description' => 'Two items together'],
            ['name' => 'Dozen', 'symbol' => 'dz', 'description' => 'Twelve items'],
            ['name' => 'Gram', 'symbol' => 'g', 'description' => 'Weight in grams'],
            ['name' => 'Centimeter', 'symbol' => 'cm', 'description' => 'Length in centimeters'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['symbol' => $unit['symbol']],
                array_merge($unit, ['status' => 'active'])
            );
        }
    }
}

