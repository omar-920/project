<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ManageUserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    /** @test */
    public function admin_can_get_all_users()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Data retrieved successfully',
                     'status code' => 200,
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'current_page',
                         'data' => [
                             '*' => ['id', 'name', 'email', 'role']
                         ],
                         'first_page_url',
                         'from',
                         'last_page',
                         'last_page_url',
                         'links',
                         'next_page_url',
                         'path',
                         'per_page',
                         'prev_page_url',
                         'to',
                         'total',
                     ],
                     'status code',
                 ]);
        $response->assertJsonPath('data.total', User::count());
        $response->assertJsonCount(User::count() > 10 ? 10 : User::count(), 'data.data'); // عدد العناصر في الصفحة الحالية
    }

    /** @test */
    public function admin_can_create_a_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'phone' => '0987654321',
            'address' => '456 New St',
            'role' => 'user',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/user/create', $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New User']);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    /** @test */
    public function admin_can_update_a_user()
    {
        $updateData = ['name' => 'Updated Name'];

        $response = $this->actingAs($this->admin, 'sanctum')->putJson('/api/user/' . $this->user->id . '/update', $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function admin_can_delete_a_user()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson('/api/user/' . $this->user->id . '/delete');

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'User Deleted!']);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }
}