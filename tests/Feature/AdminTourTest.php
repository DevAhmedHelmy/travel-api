<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a public user cannot access the admin travel endpoint.
     */
    public function test_public_user_can_not_access_admin_travel_tours(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $response = $this->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_add_new_tour(): void
    {
        $this->seed(RoleSeeder::class);
        $travel = Travel::factory()->create(['is_public' => true]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(403);
    }

    public function test_admin_can_add_new_tour_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $travel = Travel::factory()->create(['is_public' => true]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'test tour',
            'price' => 100,
            'starting_date' => '2023-07-05',
            'ending_date' => '2023-07-06',
        ]);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'price',
                'starting_date',
                'ending_date',
            ],
        ]);
    }

    public function test_admin_can_add_new_tour_with_invalid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $travel = Travel::factory()->create(['is_public' => true]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');
        $response->assertStatus(422);
    }
}
