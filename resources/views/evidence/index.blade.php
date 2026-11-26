<x-app-layout>
    <x-slot name="header">{{ __('Evidence') }}</x-slot>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('evidence.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-5 col-md-4">
                    <label for="case_id" class="form-label small text-secondary">Case</label>
                    <select id="case_id" name="case_id" class="form-select">
                        <option value="">All cases</option>
                        @foreach ($cases as $case)
                            <option value="{{ $case->id }}" @selected((string) request('case_id') === (string) $case->id)>
                                {{ $case->case_number }}: {{ $case->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-4 col-md-3">
                    <label for="status" class="form-label small text-secondary">Registration Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Evidence ID</th>
                        <th>Case</th>
                        <th>Original Filename</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evidence as $item)
                        <tr>
                            <td class="text-nowrap">{{ $item->evidence_id }}</td>
                            <td>{{ $item->case->case_number }}</td>
                            <td>{{ $item->original_filename }}</td>
                            <td class="text-nowrap">{{ number_format($item->file_size / 1024, 1) }} KB</td>
                            <td>{{ $item->uploader->name }}</td>
                            <td><span class="badge {{ $item->registration_status->badgeClass() }}">{{ $item->registration_status->label() }}</span></td>
                            <td class="text-nowrap">{{ $item->created_at->format('M j, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('evidence.show', $item) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">
                                No evidence found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($evidence->hasPages())
            <div class="card-body border-top">
                {{ $evidence->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
