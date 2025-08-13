<?php

namespace Tests\Feature;

use App\Jobs\SendMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser($overrides = [])
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ], $overrides));
    }

    public function index_returns_user_and_users_list()
    {
        $user = $this->createUser();
        $this->createUser(['name' => 'User2']);
        $this->createUser(['name' => 'User3']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertViewIs('home') // Controller এ 'home' আছে
            ->assertViewHas('user', function ($viewUser) use ($user) {
                return $viewUser->id === $user->id;
            })
            ->assertViewHas('users', function ($users) use ($user) {
                return $users->where('id', $user->id)->count() === 0;
            });
    }

    public function messages_returns_existing_chat_messages()
    {
        $user = $this->createUser();
        $chatUser = $this->createUser();

        $chat = Chat::create([
            'sender_id' => $user->id,
            'receiver_id' => $chatUser->id
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'text' => 'Hello'
        ]);

        $this->actingAs($user)
            ->getJson(route('messages', ['chat_user_id' => $chatUser->id]))
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function messages_returns_empty_if_no_chat()
    {
        $user = $this->createUser();
        $chatUser = $this->createUser();

        $this->actingAs($user)
            ->getJson(route('messages', ['chat_user_id' => $chatUser->id]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function chat_returns_view_with_users()
    {
        $user = $this->createUser();
        $chatUser = $this->createUser();

        $this->actingAs($user)
            ->get(route('messages.chat', ['user_id' => $chatUser->id]))
            ->assertStatus(200)
            ->assertViewIs('chat')
            ->assertViewHasAll(['user', 'chatUser']);
    }

    public function message_creates_chat_if_not_exists_and_dispatches_job()
    {
        Queue::fake();
        Storage::fake('public');

        $user = $this->createUser();
        $chatUser = $this->createUser();

        $file = UploadedFile::fake()->image('photo.jpg');

        $this->actingAs($user)
            ->postJson(route('message'), [
                'user_id' => $chatUser->id,
                'text' => 'Hello there',
                'photo' => $file
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('chats', [
            'sender_id' => $user->id,
            'receiver_id' => $chatUser->id
        ]);

        $this->assertDatabaseHas('messages', [
            'text' => 'Hello there',
            'user_id' => $user->id
        ]);

        Storage::disk('public')->assertExists('photos/' . $file->hashName());

        Queue::assertPushed(SendMessage::class);
    }
}
