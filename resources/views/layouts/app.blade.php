<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($header) ? $header.' - ' : '' }}{{ config('app.name') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @fonts

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-body-secondary">
        <div class="d-flex" style="min-height: 100vh;">
            @include('layouts.partials.sidebar')

            <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
                @include('layouts.partials.topbar')

                <main class="flex-grow-1 p-3 p-lg-4">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
