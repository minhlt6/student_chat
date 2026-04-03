<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-[#4a7b9d]">
    <div class="min-h-screen flex flex-col md:flex-row">

        <div class="md:w-5/12 lg:w-1/2 flex flex-col justify-center items-center p-8 relative min-h-[300px] md:min-h-screen">
            <div class="text-center text-white mb-8 z-10 mt-10 md:mt-0">
                <img src="{{ asset('images/logo-tlu.png') }}" alt="Logo TLU" class="w-20 h-20 md:w-24 md:h-24 mx-auto mb-4 object-contain drop-shadow-lg">
                <h2 class="text-xl md:text-2xl font-serif tracking-wide drop-shadow-md">Đại học Thủy lợi</h2>
            </div>

            <div class="relative w-full max-w-xs md:max-w-sm z-10 flex-1 flex items-center justify-center">
                <img src="{{ asset('images/3d-edu.png') }}" alt="Illustration" class="w-full h-auto object-contain drop-shadow-2xl hover:-translate-y-2 transition-transform duration-500">
            </div>
        </div>

        <div class="w-full md:w-7/12 lg:w-1/2 bg-white flex justify-center items-center p-8 sm:p-12 md:rounded-l-[4rem] shadow-2xl relative z-20 min-h-screen md:min-h-0">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>
</html>