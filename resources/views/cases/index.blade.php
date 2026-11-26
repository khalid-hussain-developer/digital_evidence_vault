<x-app-layout>
    <x-slot name="header">{{ __('Cases') }}</x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('cases.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-4">
                    <label for="search" class="form-label small text-secondary">Search</label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Case number or title"
                    >
                </div>

                <div class="col-sm-4 col-md-3">
                    <label for="status" class="form-label small text-secondary">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-2 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>

                <div class="col-sm-12 col-md-3 text-md-end">
                    @can('create', \App\Models\CaseRecord::class)
                        <a href="{{ route('cases.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>New Case
                        </a>
                    @endcan
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Case Number</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $case)
                        <tr>
                            <td class="text-nowrap">{{ $case->case_number }}</td>
                            <td>{{ $case->title }}</td>
                            <td><span class="badge {{ $case->status->badgeClass() }}">{{ $case->status->label() }}</span></td>
                            <td>{{ $case->creator->name }}</td>
                            <td class="text-nowrap">{{ $case->created_at->format('M j, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                No cases found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cases->hasPages())
            <div class="card-body border-top">
                {{ $cases->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
