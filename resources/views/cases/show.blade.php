<x-app-layout>
    <x-slot name="header">{{ $case->case_number }}</x-slot>

    <a href="{{ route('cases.index') }}" class="text-decoration-none text-secondary small d-inline-block mb-3">
        <i class="bi bi-arrow-left me-1"></i>Back to Cases
    </a>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h5 mb-1">{{ $case->title }}</h2>
                            <span class="badge {{ $case->status->badgeClass() }}">{{ $case->status->label() }}</span>
                        </div>

                        @can('update', $case)
                            <a href="{{ route('cases.edit', $case) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                        @endcan
                    </div>

                    <p class="text-secondary mb-0">
                        {{ $case->description ?: 'No description provided.' }}
                    </p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h6 mb-1">Case Details</h3>
                    <dl class="row small mb-0">
                        <dt class="col-sm-4 text-secondary">Case Number</dt>
                        <dd class="col-sm-8">{{ $case->case_number }}</dd>

                        <dt class="col-sm-4 text-secondary">Created By</dt>
                        <dd class="col-sm-8">{{ $case->creator->name }}</dd>

                        <dt class="col-sm-4 text-secondary">Created On</dt>
                        <dd class="col-sm-8">{{ $case->created_at->format('M j, Y \a\t g:i A') }}</dd>

                        <dt class="col-sm-4 text-secondary">Last Updated</dt>
                        <dd class="col-sm-8">{{ $case->updated_at->format('M j, Y \a\t g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 mb-0">Evidence</h3>

                        @if ($canUploadEvidence)
                            <a href="{{ route('cases.evidence.create', $case) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-upload me-1"></i>Upload Evidence
                            </a>
                        @endif
                    </div>

                    @if ($case->evidence->isEmpty())
                        <p class="small text-secondary mb-0">No evidence has been uploaded to this case yet.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($case->evidence as $item)
                                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <a href="{{ route('evidence.show', $item) }}">{{ $item->original_filename }}</a>
                                    <span class="badge {{ $item->registration_status->badgeClass() }}">
                                        {{ $item->registration_status->label() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 mb-3">Assigned Users</h3>

                    <ul class="list-unstyled mb-3">
                        @foreach ($case->assignedUsers as $user)
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div>{{ $user->name }}</div>
                                    <span class="badge {{ $user->role->badgeClass() }}">{{ $user->role->label() }}</span>
                                </div>

                                @if ($canManageAssignments && $user->id !== $case->created_by)
                                    <form method="POST" action="{{ route('cases.assignments.destroy', [$case, $user]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove from case">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($canManageAssignments && $assignableUsers->isNotEmpty())
                        <form method="POST" action="{{ route('cases.assignments.store', $case) }}" class="d-flex gap-2">
                            @csrf
                            <select name="user_id" class="form-select form-select-sm" required>
                                <option value="" selected disabled>Assign a user...</option>
                                @foreach ($assignableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->label() }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary text-nowrap">Assign</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
