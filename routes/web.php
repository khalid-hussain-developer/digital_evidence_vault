<?php

use App\Http\Controllers\BlockchainController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('cases', CaseController::class)->except(['destroy']);
    Route::post('cases/{case}/assignments', [CaseController::class, 'storeAssignment'])->name('cases.assignments.store');
    Route::delete('cases/{case}/assignments/{user}', [CaseController::class, 'destroyAssignment'])->name('cases.assignments.destroy');

    Route::get('cases/{case}/evidence/create', [EvidenceController::class, 'create'])->name('cases.evidence.create');
    Route::post('cases/{case}/evidence', [EvidenceController::class, 'store'])->name('cases.evidence.store');

    Route::get('evidence', [EvidenceController::class, 'index'])->name('evidence.index');
    Route::get('evidence/{evidence}', [EvidenceController::class, 'show'])->name('evidence.show');
    Route::get('evidence/{evidence}/download', [EvidenceController::class, 'download'])->name('evidence.download');

    Route::post('evidence/{evidence}/blockchain/prepare', [BlockchainController::class, 'prepare'])->name('evidence.blockchain.prepare');
    Route::post('evidence/{evidence}/blockchain/transactions', [BlockchainController::class, 'storeTransaction'])->name('evidence.blockchain.transactions.store');
    Route::post('evidence/{evidence}/blockchain/transactions/{blockchainTransaction}/refresh', [BlockchainController::class, 'refreshTransaction'])->name('evidence.blockchain.transactions.refresh');

    Route::get('wallet/challenge', [WalletController::class, 'challenge'])->name('wallet.challenge');
    Route::post('wallet/verify', [WalletController::class, 'verify'])->name('wallet.verify');
});

require __DIR__.'/auth.php';
