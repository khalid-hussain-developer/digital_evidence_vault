<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Enums\UserRole;
use App\Http\Requests\Cases\StoreCaseRequest;
use App\Http\Requests\Cases\UpdateCaseRequest;
use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CaseController extends Controller
{
    /**
     * List the cases visible to the current user, with optional search
     * and status filtering.
     */
    public function index(Request $request): View
    {
        $cases = CaseRecord::query()
            ->visibleTo($request->user())
            ->with('creator')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('case_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cases.index', [
            'cases' => $cases,
            'statuses' => CaseStatus::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', CaseRecord::class);

        return view('cases.create', [
            'statuses' => CaseStatus::cases(),
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(StoreCaseRequest $request): RedirectResponse
    {
        $case = CaseRecord::create([
            ...$request->safe()->only(['case_number', 'title', 'description', 'status']),
            'created_by' => $request->user()->id,
        ]);

        $case->assignedUsers()->attach($request->user()->id, [
            'assigned_by' => $request->user()->id,
        ]);

        foreach ($request->validated('assigned_user_ids', []) as $userId) {
            if ((int) $userId !== $request->user()->id) {
                $case->assignedUsers()->attach($userId, ['assigned_by' => $request->user()->id]);
            }
        }

        return redirect()->route('cases.show', $case)
            ->with('status', 'Case created successfully.');
    }

    public function show(Request $request, CaseRecord $case): View
    {
        Gate::authorize('view', $case);

        $case->load(['creator', 'assignedUsers', 'evidence' => fn ($query) => $query->latest()]);

        return view('cases.show', [
            'case' => $case,
            'canManageAssignments' => $request->user()->can('manageAssignments', $case),
            'canUploadEvidence' => $request->user()->can('uploadEvidence', $case),
            'assignableUsers' => $this->assignableUsers()->reject(
                fn (User $user) => $case->assignedUsers->contains('id', $user->id)
            ),
        ]);
    }

    public function edit(CaseRecord $case): View
    {
        Gate::authorize('update', $case);

        return view('cases.edit', [
            'case' => $case,
            'statuses' => CaseStatus::cases(),
        ]);
    }

    public function update(UpdateCaseRequest $request, CaseRecord $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.show', $case)
            ->with('status', 'Case updated successfully.');
    }

    /**
     * Assign an additional user to the case's custody/visibility list.
     */
    public function storeAssignment(Request $request, CaseRecord $case): RedirectResponse
    {
        Gate::authorize('manageAssignments', $case);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $case->assignedUsers()->syncWithoutDetaching([
            $validated['user_id'] => ['assigned_by' => $request->user()->id],
        ]);

        return redirect()->route('cases.show', $case)
            ->with('status', 'User assigned to case.');
    }

    public function destroyAssignment(Request $request, CaseRecord $case, User $user): RedirectResponse
    {
        Gate::authorize('manageAssignments', $case);

        if ($user->id === $case->created_by) {
            return back()->withErrors(['assignment' => 'The case creator cannot be removed from the case.']);
        }

        $case->assignedUsers()->detach($user->id);

        return redirect()->route('cases.show', $case)
            ->with('status', 'User removed from case.');
    }

    /**
     * Users eligible to be assigned to a case. Admin accounts are excluded
     * from the list — Admins already see every case and assigning them
     * individually would be meaningless.
     *
     * @return Collection<int, User>
     */
    protected function assignableUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where('role', '!=', UserRole::Admin->value)
            ->orderBy('name')
            ->get();
    }
}
