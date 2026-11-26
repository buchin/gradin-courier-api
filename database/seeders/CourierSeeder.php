<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samples = [
            [
                'name' => 'Andi Pratama',
                'phone' => '081234567801',
                'email' => 'andi.pratama@example.com',
                'vehicle_type' => 'bicycle',
                'license_plate' => null,
                'level' => 1,
                'is_active' => true,
                'registered_at' => now()->subDays(30),
            ],
            [
                'name' => 'Bambang Susilo',
                'phone' => '081234567802',
                'email' => 'bambang.susilo@example.com',
                'vehicle_type' => 'motorcycle',
                'license_plate' => 'L 1234 AB',
                'level' => 2,
                'is_active' => true,
                'registered_at' => now()->subDays(20),
            ],
            [
                'name' => 'Budiono Hadi Agung',
                'phone' => '081234567803',
                'email' => 'budiono.agung@example.com',
                'vehicle_type' => 'motorcycle',
                'license_plate' => 'L 5678 CD',
                'level' => 3,
                'is_active' => true,
                'registered_at' => now()->subDays(10),
            ],
            [
                'name' => 'Citra Dewi Lestari',
                'phone' => '081234567804',
                'email' => 'citra.dewi@example.com',
                'vehicle_type' => 'car',
                'license_plate' => 'L 9012 EF',
                'level' => 4,
                'is_active' => true,
                'registered_at' => now()->subDays(5),
            ],
            [
                'name' => 'Dedi Kurniawan',
                'phone' => '081234567805',
                'email' => 'dedi.kurniawan@example.com',
                'vehicle_type' => 'van',
                'license_plate' => 'L 3456 GH',
                'level' => 5,
                'is_active' => true,
                'registered_at' => now()->subDays(1),
            ],
        ];

        foreach ($samples as $sample) {
            Courier::updateOrCreate(
                ['email' => $sample['email']],
                $sample,
            );
        }

        Courier::factory()->count(15)->create();
    }
}
