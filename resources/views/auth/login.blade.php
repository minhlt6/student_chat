<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session("status")" />

    <h2
        class="mb-9 text-center font-serif text-4xl font-bold leading-tight tracking-tight text-[#223a57] md:text-[50px]">
        Đăng nhập</h2>

    <form method="POST" action="{{ route("login") }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Tài khoản</label>
            <input id="email"
                class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="Nhập email của bạn" />
            <x-input-error :messages="$errors->get("email")" class="mt-2" />
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Mật khẩu</label>
            <div class="relative">
                <input id="password"
                    class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 pr-12 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                    type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />

                <button type="button" onclick="togglePassword()"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 transition-colors hover:text-[#4a7b9d] focus:outline-none">
                    <svg id="eye-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get("password")" class="mt-2" />
        </div>

        <div class="pt-2">
            @if (Route::has("password.request"))
                <div class="mb-4 text-right">
                    <a class="text-sm font-medium text-[#4a7b9d] hover:underline"
                        href="{{ route("password.request") }}">
                        Quên mật khẩu?
                    </a>
                </div>
            @endif

            <button type="submit"
                class="w-full rounded-2xl bg-[#4a7b9d] px-4 py-4 font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-[#396381] hover:shadow-xl">
                Đăng nhập
            </button>

            <div class="mt-4 text-center">
                <a href="{{ route("register") }}" class="text-sm font-medium text-[#4a7b9d] hover:underline">
                    Chưa có tài khoản? Đăng ký
                </a>
            </div>
        </div>

        <div class="mt-8 space-y-2 border-t border-gray-100 pt-6 text-left text-xs text-[#d32f2f]">
            <p class="italic">(*) Đăng nhập bằng tài khoản/mật khẩu của <strong class="font-bold">trang khai báo thông
                    tin thí sinh</strong></p>
            <p class="italic">(*) Email + Điện thoại hỗ trợ:</p>
            <p class="font-bold">phanmemttth@tlu.edu.vn - 0865903174</p>
            <a href="#" class="mt-2 flex items-center gap-1 font-bold uppercase text-[#d32f2f] hover:underline">
                Hướng dẫn sử dụng
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
            </a>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                // Chuyển sang dạng text để nhìn thấy chữ
                passwordInput.type = 'text';
                // Đổi icon sang Mắt nhắm (Mắt gạch chéo)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                `;
            } else {
                // Chuyển lại thành dạng dấu chấm
                passwordInput.type = 'password';
                // Đổi lại thành icon Mắt mở
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }
    </script>
</x-guest-layout>
