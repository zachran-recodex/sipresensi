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

        Location::factory()->create([
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Asia Afrika No. 45, Bandung',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        Location::factory()->create([
            'name' => 'Cabang Surabaya',
            'address' => 'Jl. Tunjungan No. 67, Surabaya',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'radius_meters' => 200,
            'is_active' => true,
        ]);

        Location::factory()->create([
            'name' => 'Cabang Medan',
            'address' => 'Jl. Sudirman No. 89, Medan',
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'radius_meters' => 120,
            'is_active' => false,
        ]);

        // Create additional random locations
        Location::factory(10)->create();
    }
}
