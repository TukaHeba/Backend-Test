<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that a user can register successfully.
     */
    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => 'password@123',
            'password_confirmation' => 'password@123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'type',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Registered successfully!',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@gmail.com',
            'role' => 'customer',
        ]);
    }

    /**
     * Test that a user can login successfully.
     */
    public function test_user_can_login(): void
    {
        $user = User::where('email', 'customer@gmail.com')->first();

        $loginData = [
            'email' => 'customer@gmail.com',
            'password' => 'password@123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'type',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Logged in successfully',
            ]);

        $this->assertNotNull($response->json('data.token'));
    }
}
