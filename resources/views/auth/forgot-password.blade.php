<x-guest-layout>
    <h1 class="h5 mb-3">Forgot your password?</h1>

    <p class="small text-secondary mb-3">
        {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                :class="$errors->has('email') ? 'is-invalid' : ''"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
