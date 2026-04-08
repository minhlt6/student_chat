<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\ChatHistory;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Đếm tổng số lượng học sinh dựa vào cột role
        $totalStudents = User::where('role', 'user')->count();

        // Bảng chat_histories có thể chưa migrate ở một số môi trường.
        $totalQuestions = Schema::hasTable('chat_histories') ? ChatHistory::count() : 0;

        $documents = [];
        $documentsLoaded = false;
        $documentListWarning = null;
        $documentListNotice = null;
        $chatbotBaseUrl = rtrim((string) config('services.chatbot.base_url'), '/');
        $shouldLoadDocuments = $request->boolean('load_documents')
            || session()->has('uploadSuccess')
            || session()->has('uploadedDocumentId');

        if ($chatbotBaseUrl === '') {
            $documentListWarning = 'Thiếu cấu hình CHATBOT_API_BASE_URL.';
        } elseif ($shouldLoadDocuments) {
            try {
                $response = Http::withoutVerifying()
                    ->acceptJson()
                    ->retry(2, 1500, null, false)
                    ->connectTimeout(10)
                    ->timeout(60)
                    ->get($chatbotBaseUrl . '/admin/documents', [
                        'limit' => 10,
                        'offset' => 0,
                    ]);

                $documentsLoaded = true;
                if ($response->successful()) {
                    $documents = $response->json('items', []);
                } else {
                    $documentListWarning = 'Không tải được danh sách tài liệu từ máy chủ AI.';
                }
            } catch (\Throwable $exception) {
                Log::warning('Không thể tải danh sách tài liệu admin', [
                    'error' => $exception->getMessage(),
                ]);
                $documentListWarning = 'Không thể kết nối máy chủ AI để lấy danh sách tài liệu.';
            }
        } else {
            $documentListNotice = 'Danh sách tài liệu chưa được đồng bộ. Nhấn "Làm mới danh sách" để tải từ máy chủ AI.';
        }

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalQuestions',
            'documents',
            'documentsLoaded',
            'documentListWarning',
            'documentListNotice'
        ));
    }

    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'documentFile' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:30720'],
        ], [
            'documentFile.max' => 'Tệp vượt quá giới hạn 30MB.',
        ]);

        $uploadedFile = $validated['documentFile'];
        $chatbotBaseUrl = rtrim((string) config('services.chatbot.base_url'), '/');

        if ($chatbotBaseUrl === '') {
            return back()->withErrors([
                'documentFile' => 'Thiếu cấu hình CHATBOT_API_BASE_URL.',
            ]);
        }

        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(120)
                ->attach(
                    'file',
                    file_get_contents($uploadedFile->getRealPath()),
                    $uploadedFile->getClientOriginalName(),
                    ['Content-Type' => $uploadedFile->getMimeType() ?: 'application/octet-stream']
                )
                ->post($chatbotBaseUrl . '/admin/documents/upload');

            if ($response->successful()) {
                $documentId = $response->json('document_id');

                return redirect()
                    ->route('admin.dashboard', ['load_documents' => 1])
                    ->with('uploadSuccess', 'Upload thành công. Tài liệu đang được xử lý.')
                    ->with('uploadedDocumentId', $documentId);
            }

            $detail = $response->json('detail');
            if (is_array($detail)) {
                $detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
            }

            return back()->withErrors([
                'documentFile' => 'Upload thất bại: ' . ($detail ?: ('HTTP ' . $response->status())),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Upload tài liệu sang AI thất bại', [
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'documentFile' => 'Không thể kết nối máy chủ AI: ' . $exception->getMessage(),
            ]);
        }
    }
}
