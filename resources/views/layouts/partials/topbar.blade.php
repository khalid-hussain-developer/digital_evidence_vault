<nav class="navbar bg-white border-bottom px-3 app-topbar-height">
    <div class="container-fluid gap-3">
        <button
            type="button"
            class="btn btn-link text-dark p-0 d-lg-none"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebar"
            aria-controls="sidebar"
            aria-label="Open navigation"
        >
            <i class="bi bi-list fs-3"></i>
        </button>

        <h1 class="h4 fw-semibold mb-0 flex-grow-1">
            {{ $header ?? '' }}
        </h1>

        <div class="dropdown">
            <button
                type="button"
                class="btn btn-light d-flex align-items-center gap-2"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <span class="dropdown-item-text small text-secondary">
                        <span class="badge {{ auth()->user()->role->badgeClass() }}">
                            {{ auth()->user()->role->label() }}
                        </span>
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i>Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
