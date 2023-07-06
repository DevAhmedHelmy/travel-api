<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // valid credentials for login
    public function test_login_returns_token_with_valid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'access_token',
        ]);
    }

    public function test_login_returns_errors_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }
}
