<x-app-layout>
    @php
        $isAuthenticated = Auth::check();
        $displayName = $isAuthenticated ? (string) Auth::user()->name : "Khach";
        $avatarLetter = strtoupper(substr($displayName, 0, 1));
        $chatContainerHeightClass = $isAuthenticated ? "h-[calc(100vh-65px)]" : "h-screen";
    @endphp

    <div class="{{ $chatContainerHeightClass }} flex overflow-hidden bg-slate-50">

        <div class="z-10 flex w-72 flex-col border-r border-gray-200 bg-white shadow-sm transition-all">
            <div class="border-b border-gray-100 p-4">
                <button onclick="startNewSession()"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#4a7b9d] px-4 py-2.5 font-medium text-white shadow-sm transition-all hover:bg-[#396381]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Cuộc trò chuyện mới
                </button>
            </div>

            <div class="flex-1 space-y-1 overflow-y-auto p-3" id="session-list">
                @if ($isAuthenticated)
                    <div class="mt-10 text-center text-sm text-gray-400">
                        <svg class="mx-auto mb-2 h-8 w-8 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Đang tải lịch sử...
                    </div>
                @else
                    <div class="mt-10 px-2 text-center text-sm text-gray-500">
                        Dang chat voi che do khach.<br>
                        Lich su se khong duoc luu.
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3 border-t border-gray-100 bg-gray-50 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#4a7b9d] font-bold text-white">
                    {{ $avatarLetter }}
                </div>
                <div class="overflow-hidden text-sm">
                    <p class="truncate font-bold text-gray-800">{{ $displayName }}</p>
                    <p class="truncate text-xs text-gray-500">
                        {{ $isAuthenticated ? "Tai khoan da dang nhap" : "Khach truy cap" }}</p>
                </div>
            </div>
        </div>

        <div class="relative flex h-full flex-1 flex-col">
            <div
                class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white/80 px-6 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-xl">🤖</div>
                    <div>
                        <h2 class="font-bold leading-tight text-gray-800">Trợ Lý AI Quy Chế Đào Tạo</h2>
                        <p class="flex items-center gap-1 text-xs font-medium text-green-600">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span> Hệ thống RAG đang hoạt động
                        </p>
                    </div>
                </div>
            </div>

            <div id="chat-box" class="flex flex-1 flex-col gap-6 overflow-y-auto scroll-smooth p-6 pb-32">
                <div class="flex max-w-[85%] items-start gap-4">
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-blue-200 bg-blue-100 text-2xl shadow-sm">
                        🤖</div>
                    <div
                        class="rounded-2xl rounded-tl-none border border-gray-100 bg-white p-4 text-sm leading-relaxed text-gray-800 shadow-sm md:text-base">
                        Chào bạn! Mình là AI hỗ trợ tra cứu quy chế học vụ của trường. Hãy đặt câu hỏi, mình sẽ đối
                        chiếu với cơ sở dữ liệu để trả lời chuẩn xác nhất!
                    </div>
                </div>
            </div>

            <div
                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-50 via-slate-50 to-transparent px-6 pb-6 pt-10">
                <div
                    class="mx-auto flex max-w-4xl items-end overflow-hidden rounded-2xl border border-gray-300 bg-white shadow-lg transition-all focus-within:border-transparent focus-within:ring-2 focus-within:ring-[#4a7b9d]">
                    <textarea id="user-input" rows="1"
                        class="max-h-32 flex-1 resize-none border-0 bg-transparent px-5 py-4 leading-relaxed text-gray-800 placeholder-gray-400 focus:ring-0"
                        placeholder="Nhập câu hỏi của bạn về quy chế... (Shift + Enter để xuống dòng)" onkeydown="handleKeyDown(event)"></textarea>

                    <button onclick="sendMessage()" id="send-btn"
                        class="m-2 flex items-center justify-center rounded-xl bg-[#4a7b9d] p-3 text-white transition-all hover:bg-[#396381] disabled:opacity-50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-center text-xs text-gray-400">AI có thể trả lời chưa chính xác. Vui lòng đối chiếu
                    với văn bản gốc nếu cần.</p>
            </div>
        </div>
    </div>

    <script>
        const userId = @json(Auth::id() ?? "guest");
        const userAvatarLetter = @json($avatarLetter);
        const isAuthenticated = @json($isAuthenticated);
        const sessionsEndpoint = "{{ route("chat.sessions", absolute: false) }}";
        const sessionMessagesEndpointTemplate =
            "{{ route("chat.session.messages", ["sessionId" => "__SESSION__"], absolute: false) }}";
        let currentSessionId = null;

        // Hàm tạo session_id ngẫu nhiên
        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0,
                    v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        // Tải danh sách lịch sử khi mở trang
        document.addEventListener('DOMContentLoaded', async () => {
            if (!isAuthenticated) {
                return;
            }

            const sessions = await loadSessions();

            if (sessions.length > 0) {
                await switchSession(sessions[0].session_id, false);
            }
        });

        async function loadSessions() {
            if (!isAuthenticated) {
                return [];
            }

            try {
                const response = await fetch(sessionsEndpoint, {
                    headers: {
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error(`Khong the tai danh sach phien chat: ${response.status}`);
                }

                const data = await response.json();
                const sessions = Array.isArray(data.sessions) ? data.sessions : [];

                const sessionList = document.getElementById('session-list');
                sessionList.innerHTML = '';

                if (sessions.length > 0) {
                    sessions.forEach(session => {
                        const isActive = session.session_id === currentSessionId;
                        sessionList.innerHTML += `
                            <button onclick="switchSession('${session.session_id}')" 
                                class="w-full text-left px-3 py-3 rounded-lg text-sm transition-colors truncate ${isActive ? 'bg-blue-50 text-[#4a7b9d] font-bold' : 'text-gray-700 hover:bg-gray-100'}">
                                <span class="mr-2">💬</span> ${session.title}
                            </button>
                        `;
                    });
                } else {
                    sessionList.innerHTML = `
                        <div class="flex flex-col items-center justify-center mt-10 text-gray-400 opacity-80">
                            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <p class="text-sm font-medium">Chưa có lịch sử</p>
                            <p class="text-xs mt-1 text-center px-4">Hãy bắt đầu cuộc trò chuyện mới với AI nhé!</p>
                        </div>`;
                }

                return sessions;
            } catch (error) {
                console.error("Lỗi tải lịch sử:", error);
                document.getElementById('session-list').innerHTML =
                    `<p class="text-xs text-red-400 text-center mt-4">Không thể tải lịch sử.</p>`;
                return [];
            }
        }

        // Hàm tạo cuộc trò chuyện mới
        function startNewSession() {
            currentSessionId = generateUUID();
            document.getElementById('chat-box').innerHTML = `
                <div class="flex items-start gap-4 max-w-[85%]">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex-shrink-0 flex items-center justify-center text-2xl shadow-sm border border-blue-200">🤖</div>
                    <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 text-gray-800 leading-relaxed text-sm md:text-base">
                        Mình đã mở một cuộc trò chuyện mới. Bạn cần hỗ trợ gì tiếp theo?
                    </div>
                </div>`;
            loadSessions(); // Làm mới UI
        }

        // Hàm chuyển đổi phiên chat
        async function switchSession(sessionId, refreshSidebar = true) {
            if (!isAuthenticated) {
                return;
            }

            currentSessionId = sessionId;
            const chatBox = document.getElementById('chat-box');

            // Hiển thị trạng thái đang tải
            chatBox.innerHTML = `
                <div class="flex justify-center items-center h-full">
                    <svg class="w-8 h-8 text-[#4a7b9d] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>`;

            if (refreshSidebar) {
                await loadSessions();
            }

            try {
                const response = await fetch(sessionMessagesEndpointTemplate.replace('__SESSION__', encodeURIComponent(
                    sessionId)), {
                    headers: {
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error(`Khong the tai lich su phien chat: ${response.status}`);
                }

                const data = await response.json();

                chatBox.innerHTML = ''; // Xóa icon loading

                if (data.messages && data.messages.length > 0) {
                    // In từng dòng tin nhắn cũ ra màn hình
                    data.messages.forEach(msg => {
                        if (msg.role === 'user') {
                            chatBox.innerHTML += `
                                <div class="flex items-start gap-4 max-w-[85%] self-end flex-row-reverse">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex-shrink-0 flex items-center justify-center text-sm shadow-sm font-bold text-gray-600 border border-gray-300">
                                        ${userAvatarLetter}
                                    </div>
                                    <div class="bg-[#4a7b9d] text-white p-4 rounded-2xl rounded-tr-none shadow-sm leading-relaxed text-sm md:text-base">
                                        ${msg.content.replace(/\n/g, "<br>")}
                                    </div>
                                </div>`;
                        } else {
                            let formattedReply = msg.content.replace(/\n/g, "<br>").replace(/\*\*(.*?)\*\*/g,
                                "<strong>$1</strong>");
                            chatBox.innerHTML += `
                                <div class="flex items-start gap-4 max-w-[85%]">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex-shrink-0 flex items-center justify-center text-2xl shadow-sm border border-blue-200">🤖</div>
                                    <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 text-gray-800 leading-relaxed text-sm md:text-base">
                                        ${formattedReply}
                                    </div>
                                </div>`;
                        }
                    });
                    chatBox.scrollTop = chatBox.scrollHeight; // Cuộn xuống đáy
                } else {
                    chatBox.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 opacity-80 mt-10">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-[#4a7b9d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <p class="text-lg font-medium text-gray-600">Đoạn chat này trống</p>
                        <p class="text-sm mt-1">Hệ thống AI đã sẵn sàng, hãy đặt câu hỏi đầu tiên!</p>
                    </div>`;
                }
            } catch (error) {
                chatBox.innerHTML =
                    `<div class="text-center text-red-400 mt-10">Không thể tải nội dung đoạn chat.</div>`;
            }
        }

        // Xử lý nhập liệu của người dùng : Shift+Enter để xuống dòng, Enter để gửi
        function handleKeyDown(e) {
            const input = document.getElementById('user-input');
            input.style.height = 'auto';
            input.style.height = (input.scrollHeight) + 'px'; // Tự động dãn cao

            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        //Gửi tin nhắn và nhận phản hồi từ AI
        async function sendMessage() {
            const inputField = document.getElementById("user-input");
            const chatBox = document.getElementById("chat-box");
            const sendBtn = document.getElementById("send-btn");
            const message = inputField.value.trim();

            if (!message) return;

            if (!currentSessionId) {
                currentSessionId = generateUUID();
            }

            // Khóa input và nút gửi để tránh bấm nhiều lần
            inputField.disabled = true;
            sendBtn.disabled = true;
            inputField.style.height = 'auto';

            // Tin nhắn của người dùng 
            chatBox.innerHTML += `
                <div class="flex items-start gap-4 max-w-[85%] self-end flex-row-reverse">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex-shrink-0 flex items-center justify-center text-sm shadow-sm font-bold text-gray-600 border border-gray-300">
                        ${userAvatarLetter}
                    </div>
                    <div class="bg-[#4a7b9d] text-white p-4 rounded-2xl rounded-tr-none shadow-sm leading-relaxed text-sm md:text-base">
                        ${message.replace(/\n/g, "<br>")}
                    </div>
                </div>`;

            inputField.value = "";
            chatBox.scrollTop = chatBox.scrollHeight;


            const typingId = "typing_" + Date.now();
            chatBox.innerHTML += `
                <div id="${typingId}" class="flex items-start gap-4 max-w-[85%]">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex-shrink-0 flex items-center justify-center text-2xl shadow-sm border border-blue-200">🤖</div>
                    <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                        <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    </div>
                </div>`;
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                //Gửi request đến route chat.send để lấy câu trả lời từ AI
                const response = await fetch("{{ route("chat.send") }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        session_id: currentSessionId,
                        user_id: userId,
                        message: message
                    })
                });

                const data = await response.json();
                const typingElement = document.getElementById(typingId);

                // Hiển thị câu trả lời của AI
                if (response.ok) {
                    let formattedReply = data.reply ? data.reply.replace(/\n/g, "<br>") : data.response.replace(/\n/g,
                        "<br>");
                    // Chuyển Markdown in đậm thành thẻ strong
                    formattedReply = formattedReply.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

                    typingElement.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex-shrink-0 flex items-center justify-center text-2xl shadow-sm border border-blue-200">🤖</div>
                        <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 text-gray-800 leading-relaxed text-sm md:text-base">
                            ${formattedReply}
                        </div>`;

                    // Tải lại để cập nhật danh sách mới nhất
                    if (isAuthenticated) {
                        loadSessions();
                    }
                } else {
                    throw new Error(data.reply || "Lỗi máy chủ");
                }
            } catch (error) {
                // Hiển thị lỗi
                document.getElementById(typingId).innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-red-100 flex-shrink-0 flex items-center justify-center text-2xl shadow-sm border border-red-200">⚠️</div>
                    <div class="bg-red-50 p-4 rounded-2xl rounded-tl-none shadow-sm border border-red-200 text-red-600 font-medium">
                        Có lỗi xảy ra khi kết nối máy chủ AI. Vui lòng thử lại.
                    </div>`;
            }

            // Mở khóa input và nút gửi sau khi nhận được phản hồi
            inputField.disabled = false;
            sendBtn.disabled = false;
            inputField.focus();
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</x-app-layout>
