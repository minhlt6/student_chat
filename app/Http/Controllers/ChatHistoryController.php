<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatHistoryController extends Controller
{
    // Hàm hiển thị giao diện Chat
    public function index()
    {
        $user = Auth::user();

        if ($user instanceof User && $user->isAdmin()) {
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
        $chatbotBaseUrl = $this->resolveChatbotBaseUrl();
        $chatbotToken = trim((string) config('services.chatbot.access_token'));

        if ($chatbotBaseUrl === '') {
            return response()->json([
                'status' => 'error',
                'reply' => 'Thieu cau hinh chatbot. Vui long kiem tra CHATBOT_API_BASE_URL hoac CHATBOT_SPACE_ENDPOINT.',
            ], 500);
        }

        try {
            $requestBuilder = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(60);

            if ($chatbotToken !== '') {
                $requestBuilder = $requestBuilder->withToken($chatbotToken);
            }

            // Bắn thẳng dữ liệu sang máy chủ AI Python
            /** @var Response $response */
            $response = $requestBuilder
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
            return response()->json([
                'status' => 'error',
                'reply' => 'Loi API HTTP Code: ' . $response->status() . ' - ' . (string) ($response->json('message') ?? $response->json('error') ?? 'Khong co chi tiet'),
            ], 500);
        } catch (\Exception $e) {
            // In ra lỗi hệ thống PHP
            return response()->json(['status' => 'error', 'reply' => 'Loi ket noi: ' . $e->getMessage()], 500);
        }
    }

    private function resolveChatbotBaseUrl(): string
    {
        $baseUrl = trim((string) config('services.chatbot.base_url'));
        if ($baseUrl !== '') {
            return $this->normalizeChatbotBaseUrl($baseUrl);
        }

        $spaceEndpoint = trim((string) config('services.chatbot.space_endpoint'));
        if ($spaceEndpoint !== '') {
            $fromEndpoint = $this->resolveBaseUrlFromSpaceEndpoint($spaceEndpoint);
            if ($fromEndpoint !== '') {
                return $fromEndpoint;
            }
        }

        $spaceId = trim((string) config('services.chatbot.space_id'));
        if ($spaceId !== '') {
            return $this->buildBaseUrlFromSpaceId($spaceId);
        }

        return '';
    }

    private function normalizeChatbotBaseUrl(string $url): string
    {
        $trimmed = rtrim(trim($url), '/');
        if ($trimmed === '') {
            return '';
        }

        if (str_contains(Str::lower($trimmed), 'huggingface.co/spaces/')) {
            $resolved = $this->resolveBaseUrlFromSpaceEndpoint($trimmed);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        return $trimmed;
    }

    private function resolveBaseUrlFromSpaceEndpoint(string $spaceEndpoint): string
    {
        $trimmed = trim($spaceEndpoint);
        if ($trimmed === '') {
            return '';
        }

        if (str_contains(Str::lower($trimmed), '.hf.space')) {
            return rtrim($trimmed, '/');
        }

        if (!str_contains(Str::lower($trimmed), 'huggingface.co/spaces/')) {
            return '';
        }

        $spacePath = Str::after($trimmed, 'huggingface.co/spaces/');
        $spacePath = trim((string) preg_replace('/\?.*$/', '', $spacePath), '/');

        if ($spacePath === '') {
            return '';
        }

        $parts = explode('/', $spacePath);
        if (count($parts) < 2) {
            return '';
        }

        $spaceId = $parts[0] . '/' . $parts[1];

        return $this->buildBaseUrlFromSpaceId($spaceId);
    }

    private function buildBaseUrlFromSpaceId(string $spaceId): string
    {
        $parts = array_values(array_filter(explode('/', trim($spaceId, '/'))));
        if (count($parts) < 2) {
            return '';
        }

        $owner = Str::slug((string) $parts[0], '-');
        $space = Str::slug((string) $parts[1], '-');

        if ($owner === '' || $space === '') {
            return '';
        }

        return 'https://' . Str::lower($owner . '-' . $space) . '.hf.space';
    }

    public function sessions()
    {
        $userId = Auth::id();

        if ($userId === null) {
            return response()->json(['sessions' => []]);
        }

        return response()->json([
            'sessions' => $this->getUserSessions((string) $userId),
        ]);
    }

    public function sessionMessages(string $sessionId)
    {
        $userId = Auth::id();

        if ($userId === null) {
            return response()->json(['messages' => []]);
        }

        return response()->json([
            'messages' => $this->getSessionMessages((string) $userId, $sessionId),
        ]);
    }

    private function getUserSessions(string $userId): array
    {
        if (Schema::hasTable('history')) {
            $rows = DB::table('history')
                ->select(['session_id', 'title', 'created_at'])
                ->where('user_id', $userId)
                ->whereNotNull('session_id')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(2000)
                ->get();

            $seen = [];
            $sessions = [];

            foreach ($rows as $row) {
                $sessionId = (string) ($row->session_id ?? '');

                if ($sessionId === '' || isset($seen[$sessionId])) {
                    continue;
                }

                $seen[$sessionId] = true;

                $title = trim((string) ($row->title ?? ''));

                if ($title === '') {
                    $title = 'Cuoc tro chuyen ' . substr($sessionId, 0, 8);
                }

                $sessions[] = [
                    'session_id' => $sessionId,
                    'title' => Str::limit($title, 80),
                    'updated_at' => $row->created_at,
                ];
            }

            return $sessions;
        }

        if (Schema::hasTable('chat_histories')) {
            $rows = DB::table('chat_histories')
                ->select(['session_id', 'question', 'created_at'])
                ->where('user_id', (int) $userId)
                ->whereNotNull('session_id')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(1000)
                ->get();

            $seen = [];
            $sessions = [];

            foreach ($rows as $row) {
                $sessionId = (string) ($row->session_id ?? '');

                if ($sessionId === '' || isset($seen[$sessionId])) {
                    continue;
                }

                $seen[$sessionId] = true;

                $fallbackTitle = trim((string) ($row->question ?? ''));

                if ($fallbackTitle === '') {
                    $fallbackTitle = 'Cuoc tro chuyen ' . substr($sessionId, 0, 8);
                }

                $sessions[] = [
                    'session_id' => $sessionId,
                    'title' => Str::limit($fallbackTitle, 80),
                    'updated_at' => $row->created_at,
                ];
            }

            return $sessions;
        }

        return [];
    }

    private function getSessionMessages(string $userId, string $sessionId): array
    {
        if (Schema::hasTable('history')) {
            return DB::table('history')
                ->select(['id', 'session_id', 'role', 'content', 'created_at', 'user_id', 'title'])
                ->where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => (int) ($row->id ?? 0),
                        'session_id' => (string) ($row->session_id ?? ''),
                        'role' => (string) ($row->role ?? 'assistant'),
                        'content' => (string) ($row->content ?? ''),
                        'created_at' => $row->created_at,
                        'user_id' => (string) ($row->user_id ?? ''),
                        'title' => (string) ($row->title ?? ''),
                    ];
                })
                ->values()
                ->all();
        }

        if (Schema::hasTable('chat_histories')) {
            $rows = DB::table('chat_histories')
                ->select(['id', 'session_id', 'question', 'answer', 'created_at', 'user_id'])
                ->where('user_id', (int) $userId)
                ->where('session_id', $sessionId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $messages = [];

            foreach ($rows as $row) {
                $messages[] = [
                    'id' => (int) ($row->id ?? 0),
                    'session_id' => (string) ($row->session_id ?? ''),
                    'role' => 'user',
                    'content' => (string) ($row->question ?? ''),
                    'created_at' => $row->created_at,
                    'user_id' => (string) ($row->user_id ?? ''),
                    'title' => '',
                ];

                $answer = trim((string) ($row->answer ?? ''));

                if ($answer !== '') {
                    $messages[] = [
                        'id' => (int) ($row->id ?? 0),
                        'session_id' => (string) ($row->session_id ?? ''),
                        'role' => 'assistant',
                        'content' => $answer,
                        'created_at' => $row->created_at,
                        'user_id' => (string) ($row->user_id ?? ''),
                        'title' => '',
                    ];
                }
            }

            return $messages;
        }

        return [];
    }
}
