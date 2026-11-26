<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_couriers_sorted_by_name_ascending_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zainal Abidin']);
        Courier::factory()->create(['name' => 'Andi Wijaya']);
        Courier::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->getJson('/api/couriers?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'phone', 'email', 'vehicle_type', 'license_plate', 'level', 'is_active', 'registered_at', 'created_at', 'updated_at'],
                ],
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('data.0.name', 'Andi Wijaya')
            ->assertJsonPath('data.1.name', 'Budi Santoso');
    }

    public function test_index_can_override_sort_by_registered_at(): void
    {
        Courier::factory()->create([
            'name' => 'Charlie',
            'registered_at' => now()->subDays(10),
        ]);
        Courier::factory()->create([
            'name' => 'Alpha',
            'registered_at' => now()->subDays(1),
        ]);
        Courier::factory()->create([
            'name' => 'Bravo',
            'registered_at' => now()->subDays(5),
        ]);

        // Ascending via ?sort=registered_at
        $ascResponse = $this->getJson('/api/couriers?sort=registered_at');
        $ascResponse->assertOk()
            ->assertJsonPath('data.0.name', 'Charlie')
            ->assertJsonPath('data.1.name', 'Bravo')
            ->assertJsonPath('data.2.name', 'Alpha');

        // Descending via ?sort_by=registered_at&sort_dir=desc
        $descResponse = $this->getJson('/api/couriers?sort_by=registered_at&sort_dir=desc');
        $descResponse->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha')
            ->assertJsonPath('data.1.name', 'Bravo')
            ->assertJsonPath('data.2.name', 'Charlie');
    }

    public function test_index_supports_multi_word_search(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        Courier::factory()->create(['name' => 'Agung Laksono']);

        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_escapes_sql_wildcard_characters_in_search(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'AndiXWijaya']);
        Courier::factory()->create(['name' => 'Andi_Wijaya']);
        Courier::factory()->create(['name' => 'Promo 50% Courier']);

        $this->getJson('/api/couriers?search='.urlencode('%'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Promo 50% Courier');

        $this->getJson('/api/couriers?search='.urlencode('Andi_Wijaya'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Andi_Wijaya');
    }

    public function test_index_filters_by_multiple_levels(): void
    {
        Courier::factory()->create(['name' => 'Level 1 Courier', 'level' => 1]);
        Courier::factory()->create(['name' => 'Level 2 Courier', 'level' => 2]);
        Courier::factory()->create(['name' => 'Level 3 Courier', 'level' => 3]);
        Courier::factory()->create(['name' => 'Level 4 Courier', 'level' => 4]);
        Courier::factory()->create(['name' => 'Level 5 Courier', 'level' => 5]);

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $levels = collect($response->json('data'))->pluck('level')->all();
        $this->assertEquals([2, 3], $levels);
    }

    public function test_show_returns_all_courier_data_or_404(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Budiono Hadi Agung',
            'level' => 3,
        ]);

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.phone', $courier->phone)
            ->assertJsonPath('data.email', $courier->email)
            ->assertJsonPath('data.vehicle_type', $courier->vehicle_type)
            ->assertJsonPath('data.license_plate', $courier->license_plate)
            ->assertJsonPath('data.level', 3);

        $this->getJson('/api/couriers/999999')
            ->assertNotFound();
    }

    public function test_store_validates_input_and_persists_courier_in_database(): void
    {
        // Validation failure (422)
        $this->postJson('/api/couriers', [
            'name' => '',
            'level' => 6,
            'email' => 'not-an-email',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'email', 'vehicle_type', 'level']);

        $payload = [
            'name' => 'Surya Saputra',
            'phone' => '081299998888',
            'email' => 'surya.saputra@example.com',
            'vehicle_type' => 'motorcycle',
            'license_plate' => 'L 7788 XY',
            'level' => 4,
            'is_active' => true,
        ];

        $this->postJson('/api/couriers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Surya Saputra')
            ->assertJsonPath('data.level', 4);

        $this->assertDatabaseHas('couriers', [
            'email' => 'surya.saputra@example.com',
            'phone' => '081299998888',
            'level' => 4,
        ]);
    }

    public function test_update_validates_input_ignores_own_unique_fields_and_updates_database(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Old Name',
            'phone' => '081211112222',
            'email' => 'old@example.com',
            'level' => 2,
        ]);

        $other = Courier::factory()->create([
            'email' => 'other@example.com',
        ]);

        // Validation failure: level out of 1-5 and duplicate email from another courier
        $this->putJson("/api/couriers/{$courier->id}", [
            'level' => 0,
            'email' => $other->email,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['level', 'email']);

        // Valid update keeping own email/phone
        $this->putJson("/api/couriers/{$courier->id}", [
            'name' => 'New Name',
            'phone' => '081211112222',
            'email' => 'old@example.com',
            'level' => 5,
        ])->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.level', 5);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'New Name',
            'level' => 5,
        ]);
    }

    public function test_destroy_deletes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();

        $this->deleteJson("/api/couriers/{$courier->id}")
            ->assertOk();

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);

        $this->deleteJson("/api/couriers/{$courier->id}")
            ->assertNotFound();
    }
}
