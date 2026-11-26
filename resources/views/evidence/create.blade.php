<x-app-layout>
    <x-slot name="header">{{ __('Upload Evidence') }}: {{ $case->case_number }}</x-slot>

    <div class="card form-narrow">
        <div class="card-body">
            <form
                id="evidence-upload-form"
                method="POST"
                action="{{ route('cases.evidence.store', $case) }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div
                    id="evidence-drop-zone"
                    class="border border-2 border-dashed rounded-3 text-center p-4 mb-3 @error('file') border-danger @enderror"
                    style="cursor: pointer;"
                >
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary"></i>
                    <p class="mb-1">Drag and drop a file here, or click to browse</p>
                    <p class="small text-secondary mb-0">
                        Supported: {{ strtoupper(implode(', ', $allowedExtensions)) }}
                        (maximum {{ number_format($maxUploadSizeKb / 1024, 0) }} MB)
                    </p>
                    <input
                        id="file"
                        name="file"
                        type="file"
                        class="d-none"
                        accept="{{ implode(',', array_map(fn ($ext) => '.'.$ext, $allowedExtensions)) }}"
                        required
                    >
                    <p id="evidence-selected-file" class="small mt-2 mb-0 fw-semibold"></p>
                </div>

                <x-input-error :messages="$errors->get('file')" />

                <p class="small text-secondary">
                    The file's SHA-256 fingerprint is calculated on the server immediately after upload and
                    cannot be supplied by the browser.
                </p>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline-secondary">Cancel</a>
                    <x-primary-button id="evidence-upload-submit">Upload Evidence</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const dropZone = document.getElementById('evidence-drop-zone');
                const fileInput = document.getElementById('file');
                const selectedFileLabel = document.getElementById('evidence-selected-file');

                const showSelectedFile = () => {
                    selectedFileLabel.textContent = fileInput.files[0] ? fileInput.files[0].name : '';
                };

                dropZone.addEventListener('click', () => fileInput.click());

                dropZone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    dropZone.classList.add('border-primary');
                });

                dropZone.addEventListener('dragleave', () => {
                    dropZone.classList.remove('border-primary');
                });

                dropZone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    dropZone.classList.remove('border-primary');

                    if (event.dataTransfer.files.length > 0) {
                        fileInput.files = event.dataTransfer.files;
                        showSelectedFile();
                    }
                });

                fileInput.addEventListener('change', showSelectedFile);
            })();
        </script>
    @endpush
</x-app-layout>
