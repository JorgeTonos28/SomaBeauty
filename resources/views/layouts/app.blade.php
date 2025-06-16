<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex">
            @include('layouts.sidebar')

            <div class="flex-1">
                @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
                <footer class="bg-gray-100 text-sm px-4 py-2 flex justify-between items-center">
                    <span>&copy; {{ date('Y') }} 3Glam</span>
                    <img src="{{ asset('images/signature.png') }}" alt="Firma" class="h-15">
                </footer>
            </div>
        </div>
    </body>
</html>
