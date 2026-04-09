<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_sessions_endpoint_returns_user_sessions(): void
    {
        $user = User::factory()->create();

        ChatHistory::create([
            'user_id' => $user->id,
            'session_id' => 'session-1',
            'question' => 'Cau hoi dau tien',
            'answer' => 'Tra loi 1',
        ]);

        ChatHistory::create([
            'user_id' => $user->id,
            'session_id' => 'session-2',
            'question' => 'Cau hoi thu hai',
            'answer' => 'Tra loi 2',
        ]);

        $response = $this->actingAs($user)->getJson(route('chat.sessions'));

        $response->assertOk();
        $response->assertJsonCount(2, 'sessions');
        $response->assertJsonFragment(['session_id' => 'session-1']);
        $response->assertJsonFragment(['session_id' => 'session-2']);
    }

    public function test_chat_session_messages_endpoint_returns_user_messages(): void
    {
        $user = User::factory()->create();

        ChatHistory::create([
            'user_id' => $user->id,
            'session_id' => 'session-abc',
            'question' => 'Xin chao',
            'answer' => 'Chao ban',
        ]);

        $response = $this->actingAs($user)->getJson(route('chat.session.messages', ['sessionId' => 'session-abc']));

        $response->assertOk();
        $response->assertJsonCount(2, 'messages');
        $response->assertJsonFragment([
            'session_id' => 'session-abc',
            'role' => 'user',
            'content' => 'Xin chao',
        ]);
        $response->assertJsonFragment([
            'session_id' => 'session-abc',
            'role' => 'assistant',
            'content' => 'Chao ban',
        ]);
    }
}
