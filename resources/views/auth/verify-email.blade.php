<x-guest-layout>
    <p class="small text-secondary mb-3">
        {{ __("Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 small">
            {{ __('A new verification link has been sent to the email address on your account.') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-secondary p-0">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
