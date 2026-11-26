@php
    // Each entry names a route. If that route hasn't been built yet in the
    // current phase, Route::has() hides it below — so the sidebar never
    // shows a link that goes nowhere. 'roles' restricts an item to the
    // listed roles; omit it to show the item to every authenticated user.
    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'cases.index', 'label' => 'Cases', 'icon' => 'bi-folder2-open'],
        ['route' => 'evidence.index', 'label' => 'Evidence', 'icon' => 'bi-file-earmark-lock2'],
        ['route' => 'transfers.index', 'label' => 'Transfers', 'icon' => 'bi-arrow-left-right'],
        ['route' => 'verification.index', 'label' => 'Verification', 'icon' => 'bi-patch-check'],
        ['route' => 'blockchain.transactions.index', 'label' => 'Blockchain Transactions', 'icon' => 'bi-link-45deg'],
        ['route' => 'audit-logs.index', 'label' => 'Audit Logs', 'icon' => 'bi-journal-text'],
        ['route' => 'users.index', 'label' => 'Users & Roles', 'icon' => 'bi-people', 'roles' => [\App\Enums\UserRole::Admin]],
    ];
@endphp

<div class="offcanvas-lg offcanvas-start bg-white border-end" tabindex="-1" id="sidebar" style="--bs-offcanvas-width: 260px;">
    <div class="offcanvas-header border-bottom d-lg-none">
        <x-application-logo class="fs-6" />
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="app-topbar-height px-3 border-bottom d-none d-lg-flex align-items-center">
            <x-application-logo />
        </div>

        <nav class="nav nav-pills flex-column gap-1 p-2 flex-grow-1">
            @foreach ($navItems as $item)
                @continue(isset($item['roles']) && ! auth()->user()->hasRole(...$item['roles']))
                @continue(! \Illuminate\Support\Facades\Route::has($item['route']))

                @php
                    $routeGroup = \Illuminate\Support\Str::before($item['route'], '.');
                    $isActive = request()->routeIs($item['route']) || request()->routeIs($routeGroup.'.*');
                @endphp

                <a
                    href="{{ route($item['route']) }}"
                    class="nav-link d-flex align-items-center gap-2 text-dark {{ $isActive ? 'active' : '' }}"
                >
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <nav class="nav nav-pills flex-column gap-1 p-2 border-top">
            <a
                href="{{ route('profile.edit') }}"
                class="nav-link d-flex align-items-center gap-2 text-dark {{ request()->routeIs('profile.*') ? 'active' : '' }}"
            >
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </nav>
    </div>
</div>
