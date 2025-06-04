<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Message;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->anotherUser = User::factory()->create(['role' => 'user']);
    }

    // User message tests
    /** @test */
    public function user_can_get_their_messages()
    {
        Message::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/messages');
        $response->assertStatus(200);
    }
    
    /** @test */
    public function user_can_create_a_message()
    {
        $messageData = [
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
            'type' => 'suggestion',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/messages', $messageData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['subject' => 'Test Subject']);

        $this->assertDatabaseHas('messages', ['subject' => 'Test Subject', 'user_id' => $this->user->id]);
    }
    
    /** @test */
    public function user_cannot_see_another_users_message()
    {
        $message = Message::factory()->create(['user_id' => $this->anotherUser->id]);
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/messages/' . $message->id);
        $response->assertStatus(403);
    }
    
    // Admin message tests
    /** @test */
    public function admin_can_get_all_messages()
    {
        Message::factory()->count(3)->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/message');
        $response->assertStatus(200);
    }
    
    /** @test */
    public function admin_can_view_any_message()
    {
        $message = Message::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/message/' . $message->id);
        $response->assertStatus(200)->assertJsonFragment(['subject' => $message->subject]);
    }
    
    /** @test */
    public function admin_can_delete_any_message()
    {
        $message = Message::factory()->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson('/api/message/' . $message->id);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Message deleted successfully.']);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }
}