<?php

namespace App\Http\Controllers;

use App\Enums\EvidenceStatus;
use App\Http\Requests\Evidence\StoreEvidenceRequest;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Services\Evidence\EvidenceUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceController extends Controller
{
    /**
     * List the evidence records visible to the current user, with optional
     * case and status filtering.
     */
    public function index(Request $request): View
    {
        $evidence = Evidence::query()
            ->visibleTo($request->user())
            ->with(['case', 'uploader'])
            ->when($request->filled('case_id'), function ($query) use ($request) {
                $query->where('case_id', $request->integer('case_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('registration_status', $request->string('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('evidence.index', [
            'evidence' => $evidence,
            'statuses' => EvidenceStatus::cases(),
            'cases' => CaseRecord::query()->visibleTo($request->user())->orderBy('title')->get(),
        ]);
    }

    public function create(CaseRecord $case): View
    {
        Gate::authorize('uploadEvidence', $case);

        return view('evidence.create', [
            'case' => $case,
            'allowedExtensions' => config('evidence.allowed_extensions'),
            'maxUploadSizeKb' => config('evidence.max_upload_size_kb'),
        ]);
    }

    public function store(
        StoreEvidenceRequest $request,
        CaseRecord $case,
        EvidenceUploadService $uploadService
    ): RedirectResponse {
        $evidence = $uploadService->upload($request->file('file'), $case, $request->user());

        return redirect()->route('evidence.show', $evidence)
            ->with('status', 'Evidence uploaded and fingerprinted successfully.');
    }

    public function show(Request $request, Evidence $evidence): View
    {
        Gate::authorize('view', $evidence);

        $evidence->load(['case', 'uploader', 'custodian', 'blockchainTransactions' => fn ($query) => $query->latest()]);

        return view('evidence.show', [
            'evidence' => $evidence,
            'canRegisterOnBlockchain' => $request->user()->can('registerOnBlockchain', $evidence),
            'contractAddress' => config('blockchain.contract_address'),
        ]);
    }

    /**
     * Stream the original evidence file to an authorized user. The file
     * never has a public URL — this controller action is the only way to
     * reach it, and it re-checks authorization on every request.
     */
    public function download(Evidence $evidence): StreamedResponse
    {
        Gate::authorize('download', $evidence);

        return Storage::disk(config('evidence.storage_disk'))->download($evidence->file_path, $evidence->original_filename);
    }
}
