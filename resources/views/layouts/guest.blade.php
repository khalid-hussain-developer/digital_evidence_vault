<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @fonts

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column align-items-center justify-content-center min-vh-100 bg-body-secondary px-3 py-5">
        <div class="mb-4">
            <a href="{{ route('login') }}" class="text-decoration-none text-dark">
                <x-application-logo />
            </a>
        </div>

        <div class="card shadow-sm w-100 auth-card">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
