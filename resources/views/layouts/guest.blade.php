<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php($settings = \App\Models\SchoolSetting::current())
        <title>{{ $settings->school_name }} &middot; SchoolHub</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-10 bg-emerald-950">
            <div class="flex flex-col items-center text-center">
                @if($settings->logo_path)
                    <img src="{{ Storage::disk('public')->url($settings->logo_path) }}" alt="{{ $settings->school_name }} logo" class="h-16 w-16 rounded-full object-cover bg-white shadow">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-700 text-xl font-bold text-white shadow">
                        {{ collect(explode(' ', $settings->school_name))->map(fn ($word) => mb_substr($word, 0, 1))->take(3)->implode('') }}
                    </div>
                @endif
                <div class="mt-4 text-lg font-semibold text-white">{{ $settings->school_name }}</div>
                @if($settings->motto)
                    <div class="text-xs text-emerald-300">{{ $settings->motto }}</div>
                @endif
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-lg overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>

            <div class="mt-6 text-xs text-emerald-400">
                Powered by <span class="font-semibold text-emerald-200">SchoolHub</span>
            </div>
        </div>
    </body>
</html>
