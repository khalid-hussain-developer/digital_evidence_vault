<x-app-layout>
    <x-slot name="header">{{ __('Settings') }}</x-slot>

    <div class="row g-3 form-wide">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 mb-1">{{ __('Account') }}</h2>
                    <p class="small text-secondary mb-2">{{ __('Your role is assigned by an administrator and cannot be changed here.') }}</p>
                    <div class="d-flex gap-2">
                        <span class="badge {{ $user->role->badgeClass() }}">{{ $user->role->label() }}</span>
                        <span class="badge border {{ $user->is_active ? 'border-success text-success' : 'border-secondary text-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Deactivated' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('profile.partials.wallet-connection')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
