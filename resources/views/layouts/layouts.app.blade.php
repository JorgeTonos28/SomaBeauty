<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CarWash - @yield('title', 'Inicio')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    @include('partials.navbar')

    <main class="py-4">
        @yield('content')
    </main>
</body>
</html>
