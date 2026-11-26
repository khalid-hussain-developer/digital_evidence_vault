<x-app-layout>
    <x-slot name="header">{{ __('Dashboard') }}</x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5 mb-0">Welcome, {{ auth()->user()->name }}</h2>
        </div>
    </div>

    @php
        // Each card only renders if its key is present in $stats — 'users'
        // is added by the controller for an Admin only, so this list works
        // unchanged for every role instead of needing a second version.
        $statCards = [
            ['key' => 'users', 'label' => 'Total Users', 'icon' => 'bi-people'],
            ['key' => 'cases', 'label' => 'Total Cases', 'icon' => 'bi-folder2-open'],
            ['key' => 'evidence', 'label' => 'Total Evidence', 'icon' => 'bi-file-earmark-lock2'],
            ['key' => 'evidence_registered', 'label' => 'Registered on Blockchain', 'icon' => 'bi-link-45deg'],
        ];
    @endphp

    <div class="row g-3 mb-3">
        @foreach ($statCards as $card)
            @continue(! array_key_exists($card['key'], $stats))

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 text-secondary small mb-2">
                            <i class="bi {{ $card['icon'] }}"></i>
                            <span>{{ $card['label'] }}</span>
                        </div>
                        <p class="h3 fw-semibold mb-0">{{ $stats[$card['key']] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @php
        // Same Route::has() guard the sidebar uses — a link only appears
        // here once the page behind it actually exists, so this never
        // points anywhere broken as later phases add more sections.
        $quickLinks = [
            ['route' => 'cases.index', 'label' => 'Cases', 'description' => 'Browse the cases you have access to', 'icon' => 'bi-folder2-open'],
            ['route' => 'evidence.index', 'label' => 'Evidence', 'description' => 'Browse uploaded evidence', 'icon' => 'bi-file-earmark-lock2'],
            ['route' => 'transfers.index', 'label' => 'Transfers', 'description' => 'Review custody transfers', 'icon' => 'bi-arrow-left-right'],
            ['route' => 'audit-logs.index', 'label' => 'Audit Logs', 'description' => 'Review recent activity', 'icon' => 'bi-journal-text'],
        ];
    @endphp

    <div class="row g-3">
        @foreach ($quickLinks as $link)
            @continue(! \Illuminate\Support\Facades\Route::has($link['route']))

            <div class="col-sm-6 col-lg-3">
                <a href="{{ route($link['route']) }}" class="card text-decoration-none h-100">
                    <div class="card-body">
                        <i class="bi {{ $link['icon'] }} fs-3 text-primary"></i>
                        <h3 class="h6 mt-2 mb-1 text-dark">{{ $link['label'] }}</h3>
                        <p class="small text-secondary mb-0">{{ $link['description'] }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</x-app-layout>
