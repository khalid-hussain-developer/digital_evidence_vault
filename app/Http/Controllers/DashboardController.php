<?php

namespace App\Http\Controllers;

use App\Enums\EvidenceStatus;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with at-a-glance counts scoped to what the
     * current user is allowed to see (everything, for an Admin).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'cases' => CaseRecord::query()->visibleTo($user)->count(),
            'evidence' => Evidence::query()->visibleTo($user)->count(),
            'evidence_registered' => Evidence::query()
                ->visibleTo($user)
                ->where('registration_status', EvidenceStatus::Registered)
                ->count(),
        ];

        if ($user->isAdmin()) {
            $stats['users'] = User::query()->count();
        }

        return view('dashboard', ['stats' => $stats]);
    }
}
