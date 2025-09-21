<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" @open-sidebar.window="sidebarOpen = true" @close-sidebar.window="sidebarOpen = false" class="min-h-screen bg-gray-100 flex flex-col md:flex-row">
            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/50 z-30 md:hidden" @click="sidebarOpen = false"></div>
            @include('layouts.sidebar')

            <div class="flex-1 flex flex-col min-h-screen">
                @include('layouts.navigation')
                @include('partials.flash-messages')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
                @php
                    $businessName = optional($appearanceSettings)->business_name;
                @endphp
                <footer class="bg-gray-100 text-sm px-4 py-2 flex justify-between items-center mt-auto">
                    <span>&copy; 2025{{ $businessName ? ' ' . $businessName : '' }}</span>
                    <img src="{{ asset('images/signature.png') }}" alt="Firma" class="h-10">
                </footer>
            </div>
        </div>
    </body>
</html>
