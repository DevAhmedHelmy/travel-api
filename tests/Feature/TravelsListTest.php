<?php

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_travels_list_return_paginated_data_correctly(): void
    {
        Travel::factory()->count(20)->create(['is_public' => true]);
        $response = $this->getJson('/api/v1/travels');
        $response->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_show_only_public_travels(): void
    {
        $public_travel = Travel::factory()->create(['is_public' => true]);
        $private_travel = Travel::factory()->create(['is_public' => false]);
        $response = $this->getJson('/api/v1/travels');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $public_travel->name)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }
}
