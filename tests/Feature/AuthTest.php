<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeUser;
use Illuminate\Auth\Notifications\VerifyEmail;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** @test */
    public function a_user_can_register_successfully()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0912345678',
            'address' => '123 Test St',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User registered successfully!',
                     'status code' => 201,
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'user' => ['id', 'name', 'email'],
                     ],
                     'status code',
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Notification::assertSentTo(
            User::whereEmail('test@example.com')->first(),
            WelcomeUser::class
        );
    }

    /** @test */
    public function registration_fails_with_validation_errors()
    {
        $userData = [
            'name' => '', 
            'email' => 'not-an-email', 
            'password' => 'short', 
            'password_confirmation' => 'mismatch', 
            'phone' => '123', 
            'address' => '', 

        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Validation errors occurred',
                     'status code' => 422,
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'name',
                         'email',
                         'password',
                         'phone',
                         'address',
                     ],
                     'status code',
                 ]);
    }


    /** @test */
    public function a_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Login successful',
                     'status code' => 200,
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'access_token',
                         'token_type',
                     ],
                     'status code',
                 ]);
        
        Notification::assertSentTo($user, function (VerifyEmail $notification, array $channels) {
            return true;
        });
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Invalid credentials',
                     'data' => null,
                     'status code' => 401,
                 ]);
    }

    /** @test */
    public function login_fails_with_validation_errors()
    {
        $loginData = [
            'email' => 'not-an-email', // Invalid email
            // Password missing
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Validation errors occurred',
                     'status code' => 422,
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'email',
                         'password', // Expecting password validation error
                     ],
                     'status code',
                 ]);
    }

    /** @test */
    public function an_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Logged out successfully',
                     'data' => null,
                     'status code' => 200,
                 ]);
        
        $this->assertCount(0, $user->tokens); // Check if the token was actually deleted
    }
}