<?php

namespace App\Http\Controllers;

use App\Services\Blockchain\WalletVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Issue a one-time challenge message for the current user to sign in
     * MetaMask. Returned as JSON — the Settings page fetches this, has
     * MetaMask sign it, then posts the signature to verify().
     */
    public function challenge(Request $request, WalletVerificationService $service): JsonResponse
    {
        return response()->json([
            'message' => $service->generateChallenge($request->user()),
        ]);
    }

    public function verify(Request $request, WalletVerificationService $service): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string'],
            'signature' => ['required', 'string'],
        ]);

        $verified = $service->verify($request->user(), $validated['address'], $validated['signature']);

        if (! $verified) {
            return response()->json([
                'verified' => false,
                'message' => 'Signature verification failed. The challenge may have expired — try again.',
            ], 422);
        }

        return response()->json([
            'verified' => true,
            'wallet_address' => $request->user()->refresh()->wallet_address,
        ]);
    }
}
