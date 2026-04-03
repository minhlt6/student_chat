<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ChatHistoryController extends Controller
{
    // Hàm hiển thị giao diện Chat
    public function index()
    {
        return view('chat');
    }

    // Hàm xử lý tin nhắn
    public function askBot(Request $request)
    {
        // Lấy tin nhắn người dùng nhập
        $userMessage = $request->input('message');
        
        // Lấy ID phiên làm việc và ID sinh viên để gửi sang máy chủ 
        $sessionId = $request->input('session_id', uniqid()); // Nếu không có session_id, tạo mới
        $userId = Auth::check() ? (string) Auth::id() : "guest";
        $chatbotBaseUrl = rtrim(config('services.chatbot.base_url'), '/');

        try {
            // Bắn thẳng dữ liệu sang máy chủ AI Python
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->post($chatbotBaseUrl . '/chat', [
                    'session_id' => $sessionId,
                    'user_id'    => $userId,   // Đã bổ sung trường này cho khớp với Python
                    'message'    => $userMessage
                ]);

            if ($response->successful()) {
                $botReply = $response->json('response');
                return response()->json([
                    'status' => 'success',
                    'reply' => $botReply
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