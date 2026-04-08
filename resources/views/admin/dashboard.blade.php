<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - RAG Dashboard</title>
    <!-- Import Tailwind CSS via CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Thêm thư viện Font Awesome cho icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal h-screen flex overflow-hidden">
    
    <!-- Sidebar / Menu cột trái -->
    <div class="w-64 bg-slate-800 text-white flex flex-col h-full shadow-lg">
        <div class="h-16 flex items-center justify-center border-b border-gray-700 font-bold text-xl px-4">
            <i class="fa-solid fa-robot mr-2"></i> RAG Admin
        </div>
        <div class="flex-1 overflow-y-auto mt-4 px-2 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
            </a>
            <a href="#kb-upload" class="block px-4 py-3 hover:bg-gray-700 rounded-lg transition">
                <i class="fa-solid fa-book-open mr-2"></i> Quản lý Tài liệu
            </a>
        </div>
        <!-- Vùng Đăng xuất -->
        <div class="p-4 border-t border-gray-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-2 bg-red-600 hover:bg-red-700 rounded-lg transition">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Vùng nội dung chính (Main Content) -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navbar -->
        <header class="h-16 bg-white shadow flex items-center justify-between px-6 z-10 w-full shrink-0">
            <h1 class="text-2xl font-semibold text-gray-800">Admin Control Panel</h1>
            <div class="flex items-center text-gray-600">
                <i class="fa-solid fa-user-shield text-xl mr-2"></i>
                <span class="font-medium">Administrator</span>
            </div>
        </header>

        <!-- Vùng cuộn chứa các Card -->
        <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-gray-50">

            @if (session('uploadSuccess'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    <p class="font-semibold">{{ session('uploadSuccess') }}</p>
                    @if (session('uploadedDocumentId'))
                        <p class="text-sm mt-1">Mã tài liệu: {{ session('uploadedDocumentId') }}</p>
                    @endif
                </div>
            @endif

            @if ($documentListWarning)
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                    {{ $documentListWarning }}
                </div>
            @endif

            @if (!empty($documentListNotice))
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                    {{ $documentListNotice }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <!-- Statistics Section (Phần thống kê) -->
            <div>
                <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2"><i class="fa-solid fa-chart-pie mr-2"></i> Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Metric Card: Total Students -->
                    <div class="bg-white rounded-xl shadow p-6 flex items-center hover:shadow-lg transition">
                        <div class="p-4 rounded-full bg-blue-100 text-blue-600">
                            <i class="fa-solid fa-users text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-gray-500 font-medium text-sm">Total Students</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $totalStudents ?? 0 }}</p>
                        </div>
                    </div>
                    
                    <!-- Metric Card: Total Questions Asked -->
                    <div class="bg-white rounded-xl shadow p-6 flex items-center hover:shadow-lg transition">
                        <div class="p-4 rounded-full bg-green-100 text-green-600">
                            <i class="fa-solid fa-comments text-3xl"></i>
                        </div>
                        <div class="ml-6">
                            <p class="text-gray-500 font-medium text-sm">Total Questions Asked</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $totalQuestions ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Knowledge Base Upload Section -->
            <div id="kb-upload" class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
                <div class="bg-indigo-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-indigo-800 flex items-center">
                        <i class="fa-solid fa-file-arrow-up mr-3 text-indigo-600"></i> Knowledge Base Upload
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Tải lên các tài liệu để huấn luyện hệ thống cho Chatbot RAG.</p>
                </div>
                
                <div class="p-6">
                    <!-- Form Upload file với enctype 'multipart/form-data' để nhận file -->
                    <form method="POST" action="{{ route('admin.upload-document') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-5">
                            <label for="documentFile" class="block text-sm font-medium text-gray-700 mb-2">Chọn tệp (.pdf, .docx, .txt)</label>
                            
                            <div class="flex items-center justify-center w-full">
                                <label for="documentFile" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 border-gray-300 hover:bg-gray-100 hover:border-indigo-400 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
                                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Nhấn để tải lên</span> hoặc kéo thả file vào đây</p>
                                        <p class="text-xs text-gray-400">PDF, DOCX, TXT</p>
                                    </div>
                                    <input id="documentFile" name="documentFile" type="file" accept=".pdf, .docx, .txt" class="hidden" required />
                                </label>
                            </div>
                                    <p class="text-xs text-gray-500 mt-2">Lưu ý: Chọn file chưa gửi lên AI server ngay. File chỉ được gửi khi bấm nút Upload & Process RAG.</p>
                                    @error('documentFile')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition flex items-center">
                                <i class="fa-solid fa-gears mr-2"></i> Upload & Process RAG
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
                <div class="bg-slate-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-folder-tree mr-3 text-slate-600"></i> Tài liệu gần đây
                    </h2>
                    <a href="{{ route('admin.dashboard', ['load_documents' => 1]) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 text-white text-sm hover:bg-slate-800 transition">
                        <i class="fa-solid fa-rotate mr-2"></i> Làm mới danh sách
                    </a>
                </div>

                <div class="p-6">
                    @if (!empty($documents))
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 border-b">
                                        <th class="py-3 pr-4">Tên tài liệu</th>
                                        <th class="py-3 pr-4">Trạng thái</th>
                                        <th class="py-3 pr-4">Chunks</th>
                                        <th class="py-3">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documents as $document)
                                        @php
                                            $status = $document['status'] ?? 'unknown';
                                            $badgeClass = $status === 'done'
                                                ? 'bg-green-100 text-green-700'
                                                : ($status === 'processing'
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : ($status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'));
                                        @endphp
                                        <tr class="border-b last:border-0">
                                            <td class="py-3 pr-4 text-gray-800">{{ $document['original_name'] ?? '-' }}</td>
                                            <td class="py-3 pr-4">
                                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                    {{ strtoupper($status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 pr-4 text-gray-700">{{ $document['total_chunks'] ?? 0 }}</td>
                                            <td class="py-3 text-gray-500">{{ $document['created_at'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @if ($documentListWarning)
                            <div class="text-sm text-amber-700">Không tải được danh sách tài liệu từ máy chủ AI. Vui lòng thử lại sau.</div>
                        @elseif (empty($documentsLoaded))
                            <div class="text-sm text-slate-600">Danh sách chưa được tải. Nhấn nút Làm mới danh sách để đồng bộ dữ liệu từ AI server.</div>
                        @else
                            <div class="text-sm text-gray-500">Chưa có tài liệu nào.</div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </main>

</body>
</html>