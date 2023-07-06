<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a public user cannot access the admin travel endpoint.
     */
    public function test_public_user_can_not_access_admin_travel(): void
    {
        $response = $this->postJson('/api/v1/admin/travels');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_add_new_travel(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels');
        $response->assertStatus(403);
    }

    public function test_admin_can_add_new_travel_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels', [
            'name' => 'test travel',
            'description' => 'test travel description',
            'is_public' => true,
            'number_of_days' => 5,
        ]);
        $response->assertStatus(201)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'number_of_days',
                    'number_of_nights',
                    'is_public',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'test travel',
        ]);
    }

    public function test_admin_cannot_add_new_travel_with_invalid_data(): void
    {

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels', [
            'name' => '',
        ]);
        $response->assertStatus(422);
    }

    public function test_updates_travel_successfully_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travel = Travel::factory()->create();
        $response = $this->actingAs($user)->putJson("/api/v1/travels/{$travel->id}", [
            'name' => 'test travel',
            'description' => 'test travel description',
            'is_public' => true,
            'number_of_days' => 5,
        ]);

        $response->assertStatus(200);
        $response = $this->get('/api/v1/travels');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'test travel',
        ]);
    }

    public function test_cannot_update_travel_with_invalid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $travel = Travel::factory()->create();
        $response = $this->actingAs($user)->putJson("/api/v1/travels/{$travel->id}");
        $response->assertStatus(422);
    }
}
