<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample locations
        Location::factory()->create([
            'name' => 'Kantor Pusat',
            'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius_meters' => 150,
            'is_active' => true,
        ]);
    }
}
