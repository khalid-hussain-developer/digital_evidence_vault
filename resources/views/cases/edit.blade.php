<x-app-layout>
    <x-slot name="header">{{ __('Edit Case') }}: {{ $case->case_number }}</x-slot>

    <div class="card form-wide">
        <div class="card-body">
            <form method="POST" action="{{ route('cases.update', $case) }}">
                @csrf
                @method('PUT')

                @include('cases.partials.fields')

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Cancel</a>
                    <x-primary-button>Save Changes</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
