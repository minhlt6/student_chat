<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config("app.name", "Laravel") }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(["resources/css/app.css", "resources/js/app.js"])
    </head>

    <body class="bg-[#4f7ea3] font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col lg:flex-row">
            <div
                class="relative flex flex-col items-center justify-center overflow-hidden px-8 py-10 lg:w-1/2 lg:py-14">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.12),transparent_45%),radial-gradient(circle_at_80%_80%,rgba(255,255,255,0.08),transparent_40%)]">
                </div>

                <div class="relative z-10 mb-10 text-center text-white">
                    <img src="{{ asset("images/logo-tlu.png") }}" alt="Logo TLU"
                        class="mx-auto mb-4 h-20 w-20 object-contain drop-shadow-lg md:h-24 md:w-24">
                    <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">Đại học Thủy lợi</h2>
                </div>

                <div
                    class="relative z-10 w-full max-w-[420px] rounded-xl border border-white/10 bg-white/5 p-7 shadow-[0_20px_55px_rgba(8,20,40,0.25)] md:p-9">
                    <img src="{{ asset("images/3d-edu.png") }}" alt="Illustration"
                        class="h-auto w-full object-contain drop-shadow-2xl">
                </div>
            </div>

            <div
                class="flex w-full items-center justify-center bg-[#f5f7fb] px-6 py-10 shadow-2xl sm:px-10 sm:py-12 lg:w-1/2 lg:rounded-l-[56px] lg:bg-white lg:px-14">
                <div class="w-full max-w-md lg:max-w-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>

</html>
