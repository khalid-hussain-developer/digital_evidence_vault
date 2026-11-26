<?php

namespace App\Services\Evidence;

use App\Enums\EvidenceStatus;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EvidenceUploadService
{
    public function __construct(
        private readonly EvidenceHashService $hashService,
    ) {}

    /**
     * Store an uploaded file as a new, immutable evidence record attached
     * to the given case. The uploader becomes the initial custodian.
     *
     * The file is hashed and stored under a server-generated name before
     * the database record is written — the client's filename is kept only
     * for display, never used to build a filesystem path.
     */
    public function upload(UploadedFile $file, CaseRecord $case, User $uploader): Evidence
    {
        $sha256Hash = $this->hashService->hashFile($file->getRealPath());

        $storedFilename = (string) Str::uuid().'.'.$file->extension();
        $storedPath = $file->storeAs("evidence/{$case->id}", $storedFilename, config('evidence.storage_disk'));

        try {
            return Evidence::create([
                'evidence_id' => $this->generateEvidenceId(),
                'case_id' => $case->id,
                'uploaded_by' => $uploader->id,
                'current_custodian_id' => $uploader->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sha256_hash' => $sha256Hash,
                'registration_status' => EvidenceStatus::Pending,
            ]);
        } catch (Throwable $exception) {
            // Don't leave an orphaned file on disk if the database write fails.
            Storage::disk(config('evidence.storage_disk'))->delete($storedPath);

            throw $exception;
        }
    }

    private function generateEvidenceId(): string
    {
        return 'EVD-'.Str::upper(Str::random(10));
    }
}
