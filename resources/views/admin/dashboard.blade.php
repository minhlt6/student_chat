<!DOCTYPE html>
<html lang="vi">

    @php
        $totalStudents = $totalStudents ?? 0;
        $totalQuestions = $totalQuestions ?? 0;
        $folders = $folders ?? [];
        $selectedFolder = $selectedFolder ?? null;
        $folderFiles = $folderFiles ?? [];
        $storageWarning = $storageWarning ?? null;
    @endphp

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Control Panel - RAG Dashboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

    <body class="flex h-screen overflow-hidden bg-gray-100 font-sans leading-normal tracking-normal">

        <div class="flex h-full w-64 flex-col bg-slate-800 text-white shadow-lg">
            <div class="flex h-16 items-center justify-center border-b border-gray-700 px-4 text-xl font-bold">
                <i class="fa-solid fa-robot mr-2"></i> RAG Admin
            </div>
            <div class="mt-4 flex-1 space-y-2 overflow-y-auto px-2">
                <a href="{{ route("admin.dashboard") }}"
                    class="block rounded-lg bg-blue-600 px-4 py-3 transition hover:bg-blue-700">
                    <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="#folder-management" class="block rounded-lg px-4 py-3 transition hover:bg-gray-700">
                    <i class="fa-solid fa-folder-tree mr-2"></i> Quản lý Năm học
                </a>
                <a href="#kb-upload" class="block rounded-lg px-4 py-3 transition hover:bg-gray-700">
                    <i class="fa-solid fa-file-arrow-up mr-2"></i> Upload tài liệu
                </a>
            </div>
            <div class="border-t border-gray-700 p-4">
                <form method="POST" action="{{ route("logout") }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-red-600 p-2 transition hover:bg-red-700">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <main class="flex flex-1 flex-col overflow-hidden">
            <header class="z-10 flex h-16 w-full shrink-0 items-center justify-between bg-white px-6 shadow">
                <h1 class="text-2xl font-semibold text-gray-800">Admin Control Panel</h1>
                <div class="flex items-center text-gray-600">
                    <i class="fa-solid fa-user-shield mr-2 text-xl"></i>
                    <span class="font-medium">Administrator</span>
                </div>
            </header>

            <div class="flex-1 space-y-8 overflow-y-auto bg-gray-50 p-6">
                @if (session("success"))
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session("success") }}
                    </div>
                @endif

                @if (session("warning"))
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                        {{ session("warning") }}
                    </div>
                @endif

                @if ($storageWarning)
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800">
                        {{ $storageWarning }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <h2 class="mb-4 border-b pb-2 text-xl font-bold text-gray-700"><i
                            class="fa-solid fa-chart-pie mr-2"></i> Statistics</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="flex items-center rounded-xl bg-white p-6 shadow transition hover:shadow-lg">
                            <div class="rounded-full bg-blue-100 p-4 text-blue-600">
                                <i class="fa-solid fa-users text-3xl"></i>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-medium text-gray-500">Total Students</p>
                                <p class="text-3xl font-bold text-gray-800">{{ $totalStudents ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="flex items-center rounded-xl bg-white p-6 shadow transition hover:shadow-lg">
                            <div class="rounded-full bg-green-100 p-4 text-green-600">
                                <i class="fa-solid fa-comments text-3xl"></i>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-medium text-gray-500">Total Questions Asked</p>
                                <p class="text-3xl font-bold text-gray-800">{{ $totalQuestions ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="folder-management" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow">
                    <div class="border-b border-gray-200 bg-slate-50 px-6 py-4">
                        <h2 class="flex items-center text-lg font-bold text-slate-800">
                            <i class="fa-solid fa-folder-tree mr-3 text-slate-600"></i> Quản lý folder năm học
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">Tạo folder mới theo năm học, ví dụ: Sổ tay sinh viên
                            2023-2024.</p>
                    </div>

                    <div class="space-y-6 p-6">
                        <form method="POST" action="{{ route("admin.folders.store") }}"
                            class="grid grid-cols-1 items-end gap-3">
                            @csrf
                            <div>
                                <label for="folder_name" class="mb-2 block text-sm font-medium text-gray-700">Tên
                                    folder năm học</label>
                                <input id="folder_name" name="folder_name" type="text" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="2024-2025" value="{{ old("folder_name") }}" />
                                @error("folder_name")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="w-fit rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700">
                                Tạo folder
                            </button>
                        </form>

                        @if (!empty($folders))
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b text-left text-gray-500">
                                            <th class="py-3 pr-4">Tên năm học</th>
                                            <th class="py-3 pr-4">Folder key</th>
                                            <th class="py-3">Chỉnh sửa tên</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($folders as $folder)
                                            <tr class="border-b last:border-0">
                                                <td class="py-3 pr-4 font-medium text-gray-800">{{ $folder["name"] }}
                                                </td>
                                                <td class="py-3 pr-4 text-gray-600">{{ $folder["key"] }}</td>
                                                <td class="py-3">
                                                    <form method="POST"
                                                        action="{{ route("admin.folders.update", ["folderKey" => $folder["key"]]) }}"
                                                        class="flex items-center gap-2">
                                                        @csrf
                                                        @method("PATCH")
                                                        <input type="text" name="folder_name"
                                                            value="{{ $folder["name"] }}"
                                                            class="w-full max-w-xs rounded-lg border border-gray-300 px-2 py-1 text-sm">
                                                        <button type="submit"
                                                            class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Lưu</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Chưa có folder nào. Hãy tạo folder năm học đầu tiên.</p>
                        @endif
                    </div>
                </div>

                <div id="kb-upload" class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow">
                    <div class="border-b border-gray-200 bg-indigo-50 px-6 py-4">
                        <h2 class="flex items-center text-lg font-bold text-indigo-800">
                            <i class="fa-solid fa-file-arrow-up mr-3 text-indigo-600"></i> Upload tài liệu theo năm học
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">Admin chọn folder năm học rồi upload tài liệu lên
                            Supabase bucket.</p>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route("admin.upload-document") }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5">
                                <label for="folder_key" class="mb-2 block text-sm font-medium text-gray-700">Chọn
                                    folder năm học</label>
                                <select id="folder_key" name="folder_key"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ empty($folders) ? "disabled" : "" }} required>
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder["key"] }}"
                                            {{ ($selectedFolder["key"] ?? "") === $folder["key"] ? "selected" : "" }}>
                                            {{ $folder["name"] }} ({{ $folder["key"] }})
                                        </option>
                                    @endforeach
                                </select>
                                @if (empty($folders))
                                    <p class="mt-2 text-sm text-red-600">Bạn cần tạo ít nhất 1 folder năm học trước khi
                                        upload file.</p>
                                @endif
                            </div>

                            <div class="mb-5">
                                <label for="documentFiles" class="mb-2 block text-sm font-medium text-gray-700">Chọn
                                    tệp (.pdf, .docx, .txt)</label>

                                <div class="flex w-full items-center justify-center">
                                    <label for="documentFiles"
                                        class="flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition hover:border-indigo-400 hover:bg-gray-100">
                                        <div class="flex flex-col items-center justify-center pb-6 pt-5">
                                            <i class="fa-solid fa-cloud-arrow-up mb-3 text-4xl text-gray-400"></i>
                                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Nhấn để
                                                    tải lên</span> hoặc kéo thả nhiều file vào đây</p>
                                            <p class="text-xs text-gray-400">PDF, DOCX, TXT (chon nhieu tep)</p>
                                        </div>
                                        <input id="documentFiles" name="documentFiles[]" type="file"
                                            accept=".pdf, .docx, .txt" class="hidden" multiple required />
                                    </label>
                                </div>

                                <div id="selected-files-panel"
                                    class="mt-3 hidden rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <p class="mb-2 text-sm font-semibold text-slate-700">Tệp đã chọn:</p>
                                    <ul id="selected-files-list"
                                        class="list-disc space-y-1 pl-5 text-sm text-slate-700">
                                    </ul>
                                </div>

                                <p class="mt-2 text-xs text-gray-500">File sẽ được lưu vào bucket Supabase theo folder
                                    năm học đã chọn.</p>
                                @error("documentFiles")
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if ($errors->has("documentFiles.*"))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first("documentFiles.*") }}</p>
                                @endif
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="{{ empty($folders) ? "opacity-50 cursor-not-allowed" : "" }} flex items-center rounded-lg bg-indigo-600 px-6 py-2 font-bold text-white shadow transition hover:bg-indigo-700"
                                    {{ empty($folders) ? "disabled" : "" }}>
                                    <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload vào Supabase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow">
                    <div
                        class="flex items-center justify-between gap-3 border-b border-gray-200 bg-slate-50 px-6 py-4">
                        <h2 class="flex items-center text-lg font-bold text-slate-800">
                            <i class="fa-solid fa-file-lines mr-3 text-slate-600"></i> Danh sách file theo năm học
                        </h2>
                        @if (!empty($folders))
                            <form method="GET" action="{{ route("admin.dashboard") }}"
                                class="flex items-center gap-2">
                                <label for="list_folder_key" class="text-sm font-medium text-slate-700">Chọn năm
                                    học</label>
                                <select id="list_folder_key" name="folder"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder["key"] }}"
                                            {{ ($selectedFolder["key"] ?? "") === $folder["key"] ? "selected" : "" }}>
                                            {{ $folder["name"] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-slate-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    Chọn năm học
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="p-6">
                        @if ($selectedFolder)
                            <p class="mb-4 text-sm text-gray-600">Đang xem folder: <span
                                    class="font-semibold">{{ $selectedFolder["name"] }}</span>
                                ({{ $selectedFolder["key"] }})</p>

                            @if (!empty($folderFiles))
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b text-left text-gray-500">
                                                <th class="py-3 pr-4">Tên tệp</th>
                                                <th class="py-3 pr-4">Kích thước</th>
                                                <th class="py-3 pr-4">Cập nhật</th>
                                                <th class="py-3">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($folderFiles as $file)
                                                <tr class="border-b last:border-0">
                                                    <td class="py-3 pr-4 text-gray-800">{{ $file["name"] }}</td>
                                                    <td class="py-3 pr-4 text-gray-700">
                                                        {{ number_format(($file["size"] ?? 0) / 1024, 2) }} KB</td>
                                                    <td class="py-3 pr-4 text-gray-600">{{ $file["updated_at"] }}</td>
                                                    <td class="py-3">
                                                        <form method="POST"
                                                            action="{{ route("admin.folders.files.delete", ["folderKey" => $selectedFolder["key"]]) }}"
                                                            onsubmit="return confirm('Bạn chắc chắn muốn xóa file này?');">
                                                            @csrf
                                                            @method("DELETE")
                                                            <input type="hidden" name="file_name"
                                                                value="{{ $file["name"] }}">
                                                            <button type="submit"
                                                                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Xóa</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-sm text-gray-500">Folder này chưa có file nào.</div>
                            @endif
                        @elseif (!empty($folders))
                            <div class="text-sm text-gray-600">Hãy chọn một năm học ở nút "Chọn năm học" phía trên để
                                xem
                                file.</div>
                        @else
                            <div class="text-sm text-gray-500">Chưa có folder năm học nào để hiển thị.</div>
                        @endif
                    </div>
                </div>

            </div>
        </main>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const fileInput = document.getElementById("documentFiles");
                const filesPanel = document.getElementById("selected-files-panel");
                const filesList = document.getElementById("selected-files-list");

                if (!fileInput || !filesPanel || !filesList) {
                    return;
                }

                fileInput.addEventListener("change", function() {
                    filesList.innerHTML = "";

                    if (!fileInput.files || fileInput.files.length === 0) {
                        filesPanel.classList.add("hidden");
                        return;
                    }

                    Array.from(fileInput.files).forEach(function(file) {
                        const item = document.createElement("li");
                        const sizeKb = (file.size / 1024).toFixed(2);
                        item.textContent = file.name + " (" + sizeKb + " KB)";
                        filesList.appendChild(item);
                    });

                    filesPanel.classList.remove("hidden");
                });
            });
        </script>

    </body>

</html>
