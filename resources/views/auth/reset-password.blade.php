<x-guest-layout>
    <h1 class="h5 mb-3">Reset password</h1>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email', $request->email)"
                :class="$errors->has('email') ? 'is-invalid' : ''"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                :class="$errors->has('password') ? 'is-invalid' : ''"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                :class="$errors->has('password_confirmation') ? 'is-invalid' : ''"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
