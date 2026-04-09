<x-guest-layout>
    <h2
        class="mb-9 text-center font-serif text-4xl font-bold leading-tight tracking-tight text-[#223a57] md:text-[50px]">
        Đăng ký tài khoản
    </h2>

    <form method="POST" action="{{ route("register") }}" class="space-y-6">
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Họ và tên</label>
            <input id="name"
                class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                type="text" name="name" value="{{ old("name") }}" required autofocus autocomplete="name"
                placeholder="Nhập họ và tên" />
            <x-input-error :messages="$errors->get("name")" class="mt-2" />
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Email</label>
            <input id="email"
                class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                type="email" name="email" value="{{ old("email") }}" required autocomplete="username"
                placeholder="Nhập email" />
            <x-input-error :messages="$errors->get("email")" class="mt-2" />
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Mật khẩu</label>
            <input id="password"
                class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get("password")" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-[#2b3b52]">Nhập lại mật
                khẩu</label>
            <input id="password_confirmation"
                class="block w-full rounded-2xl border border-[#d2dae5] bg-[#f7f9fc] px-4 py-3.5 text-[#243347] shadow-sm transition-colors focus:border-[#4a7b9d] focus:ring-[#4a7b9d]"
                type="password" name="password_confirmation" required autocomplete="new-password"
                placeholder="••••••••" />
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full rounded-2xl bg-[#4a7b9d] px-4 py-4 font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-[#396381] hover:shadow-xl">
                Tạo tài khoản
            </button>
        </div>

        <div class="pt-2 text-center text-sm">
            <a href="{{ route("login") }}" class="font-medium text-[#4a7b9d] hover:underline">Đã có tài khoản? Đăng
                nhập</a>
        </div>
    </form>
</x-guest-layout>
