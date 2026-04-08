<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatHistoryController extends Controller
{
    // Hàm hiển thị giao diện Chat
    public function index()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('chat');
    }

    // Hàm xử lý tin nhắn
    public function askBot(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'session_id' => ['nullable', 'string', 'max:255'],
        ]);

        $userMessage = trim($validated['message']);

        // Lấy ID phiên làm việc và ID sinh viên để gửi sang máy chủ 
        $sessionId = (string) ($validated['session_id'] ?? (string) Str::uuid());
        $userId = Auth::check() ? (string) Auth::id() : "guest";
        $chatbotBaseUrl = rtrim((string) config('services.chatbot.base_url'), '/');

        if ($chatbotBaseUrl === '') {
            return response()->json([
                'status' => 'error',
                'reply' => 'Thiếu cấu hình CHATBOT_API_BASE_URL',
            ], 500);
        }

        try {
            // Bắn thẳng dữ liệu sang máy chủ AI Python
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(60)
                ->post($chatbotBaseUrl . '/chat', [
                    'session_id' => $sessionId,
                    'user_id'    => $userId,   // Đã bổ sung trường này cho khớp với Python
                    'message'    => $userMessage
                ]);

            if ($response->successful()) {
                $botReply = (string) ($response->json('response') ?? '');

                if ($botReply === '') {
                    $botReply = (string) ($response->json('reply') ?? '');
                }

                if (Schema::hasTable('chat_histories')) {
                    ChatHistory::create([
                        'user_id' => Auth::id(),
                        'session_id' => $sessionId,
                        'question' => $userMessage,
                        'answer' => $botReply,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'reply' => $botReply,
                    'session_id' => $sessionId,
                ]);
            }

            // In ra lỗi thực sự từ server API
            return response()->json(['status' => 'error', 'reply' => 'Lỗi API HTTP Code: ' . $response->status()], 500);

        } catch (\Exception $e) {
            // In ra lỗi hệ thống PHP
            return response()->json(['status' => 'error', 'reply' => 'Lỗi kết nối: ' . $e->getMessage()], 500);
        }
    }
}