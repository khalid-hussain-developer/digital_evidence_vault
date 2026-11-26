<x-app-layout>
    <x-slot name="header">{{ __('New Case') }}</x-slot>

    <div class="card form-wide">
        <div class="card-body">
            <form method="POST" action="{{ route('cases.store') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="case_number" value="Case Number" />
                    <x-text-input
                        id="case_number"
                        name="case_number"
                        type="text"
                        :value="old('case_number')"
                        :class="$errors->has('case_number') ? 'is-invalid' : ''"
                        required
                        autofocus
                        placeholder="e.g. CASE-2026-001"
                    />
                    <x-input-error :messages="$errors->get('case_number')" />
                </div>

                @include('cases.partials.fields')

                <div class="mb-3">
                    <x-input-label value="Assign Users" />
                    <p class="small text-secondary mb-2">You are automatically assigned as the case creator.</p>
                    <div class="row">
                        @forelse ($assignableUsers as $user)
                            <div class="col-sm-6 form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="assigned_user_ids[]"
                                    id="user-{{ $user->id }}"
                                    value="{{ $user->id }}"
                                    @checked(in_array((string) $user->id, old('assigned_user_ids', []), true))
                                >
                                <label for="user-{{ $user->id }}" class="form-check-label">
                                    {{ $user->name }}
                                    <span class="badge {{ $user->role->badgeClass() }}">{{ $user->role->label() }}</span>
                                </label>
                            </div>
                        @empty
                            <p class="small text-secondary">No other users are available to assign yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <x-primary-button>Create Case</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
