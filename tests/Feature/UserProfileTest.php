<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make('currentpassword'),
        ]);
        $this->token = $this->user->createToken('test_token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_view_their_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'status code' => 200,
                'data' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'address' => $this->user->address,
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_their_profile_info()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
            'phone' => '0987654321',
            'address' => '456 Updated St',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/profile/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Resource updated successfully',
                'status code' => 200,
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated.email@example.com',
                    'phone' => '0987654321',
                    'address' => '456 Updated St',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_their_password()
    {
        $updateData = [
            'current_password' => 'currentpassword',
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'newsecurepassword',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/profile/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Resource updated successfully',
                'status code' => 200,
            ]);

        $this->assertTrue(Hash::check('newsecurepassword', $this->user->fresh()->password));
    }

    /** @test */
    public function updating_password_fails_with_incorrect_current_password()
    {
        $updateData = [
            'current_password' => 'wrongcurrentpassword',
            'new_password' => 'newsecurepassword',
            'new_password_confirmation' => 'newsecurepassword',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/profile/update', $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation errors occurred',
                'status code' => 422,
                'data' => [
                    // تم التعديل هنا: قيمة نصية بدلاً من مصفوفة
                    'current_password' => 'Current password is not correct.'
                ]
            ]);
    }

    /** @test */
    public function profile_update_fails_with_validation_errors()
    {
        $updateData = [
            'email' => 'not-an-email', // Invalid email format
            'phone' => '123',          // Invalid phone format
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/profile/update', $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation errors occurred',
                'status code' => 422,
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['email', 'phone'], // Expecting errors for these fields
                'status code',
            ]);
    }
    
    /** @test */
    public function profile_update_fails_if_email_is_taken_by_another_user()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $updateData = [
            'email' => 'existing@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/profile/update', $updateData);
        
        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation errors occurred',
                'status code' => 422,
                'data' => [
                    'email' => ['The email has already been taken.']
                ]
            ]);
    }


    /** @test */
    public function authenticated_user_can_delete_their_account()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/profile/delete');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Resource deleted successfully',
                'status code' => 200,
            ]);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
        // Optionally, check if tokens are deleted if your controller handles that explicitly for this route
        // $this->assertCount(0, $this->user->tokens()->get()); // This would require $this->user->refresh() or similar if tokens are loaded lazily
    }
}