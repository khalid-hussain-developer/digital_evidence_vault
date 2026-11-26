<x-app-layout>
    <x-slot name="header">{{ $evidence->evidence_id }}</x-slot>

    <a href="{{ route('evidence.index') }}" class="text-decoration-none text-secondary small d-inline-block mb-3">
        <i class="bi bi-arrow-left me-1"></i>Back to Evidence
    </a>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="h5 mb-1">{{ $evidence->original_filename }}</h2>
                            <span class="badge {{ $evidence->registration_status->badgeClass() }}">
                                {{ $evidence->registration_status->label() }}
                            </span>
                        </div>

                        <a href="{{ route('evidence.download', $evidence) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                    </div>

                    <p class="small text-secondary mb-0">
                        Belongs to case
                        <a href="{{ route('cases.show', $evidence->case) }}">{{ $evidence->case->case_number }}: {{ $evidence->case->title }}</a>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="h6 mb-3">File Details</h3>
                    <dl class="row small mb-0">
                        <dt class="col-sm-4 text-secondary">Evidence ID</dt>
                        <dd class="col-sm-8">{{ $evidence->evidence_id }}</dd>

                        <dt class="col-sm-4 text-secondary">File Type</dt>
                        <dd class="col-sm-8">{{ $evidence->mime_type }}</dd>

                        <dt class="col-sm-4 text-secondary">File Size</dt>
                        <dd class="col-sm-8">{{ number_format($evidence->file_size / 1024, 1) }} KB</dd>

                        <dt class="col-sm-4 text-secondary">SHA-256 Fingerprint</dt>
                        <dd class="col-sm-8">
                            <code class="small text-break">{{ $evidence->sha256_hash }}</code>
                        </dd>

                        <dt class="col-sm-4 text-secondary">Uploaded By</dt>
                        <dd class="col-sm-8">{{ $evidence->uploader->name }}</dd>

                        <dt class="col-sm-4 text-secondary">Uploaded On</dt>
                        <dd class="col-sm-8">{{ $evidence->created_at->format('M j, Y \a\t g:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h6 mb-3">Custody</h3>
                    <p class="small text-secondary mb-1">Current Custodian</p>
                    <p class="mb-0">{{ $evidence->custodian->name }}</p>
                </div>
            </div>

            @include('evidence.partials.blockchain-registration')
        </div>
    </div>
</x-app-layout>
