<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyController extends Controller
{
    /**
     * Show the public verification page
     */
    public function show()
    {
        return view('verify.show');
    }

    /**
     * Look up a proposal by proposal number
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'proposal_no' => 'required|string|max:50'
        ]);

        $proposalNo = strtoupper(trim($request->proposal_no));
        
        // Find the proposal (exclude drafts from public verification)
        $proposal = Proposal::where('proposal_no', $proposalNo)
            ->where('status', '!=', 'draft')
            ->first();

        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found. Please check the number and try again.'
            ], 404);
        }

        // Check if proposal has a phone number for OTP
        if (!$proposal->client_phone) {
            return response()->json([
                'success' => false,
                'message' => 'This proposal does not have a registered phone number for verification.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'proposal_no' => $proposal->proposal_no,
                'masked_phone' => $proposal->masked_phone,
                'has_phone' => !empty($proposal->client_phone)
            ]
        ]);
    }

    /**
     * Send OTP to the proposal's registered phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'proposal_no' => 'required|string|max:50'
        ]);

        $proposalNo = strtoupper(trim($request->proposal_no));
        
        $proposal = Proposal::where('proposal_no', $proposalNo)
            ->where('status', '!=', 'draft')
            ->first();

        if (!$proposal || !$proposal->client_phone) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found or no phone number registered.'
            ], 404);
        }

        try {
            // Generate OTP
            $otp = $proposal->generateOtp();
            
            // Send SMS using SMS service
            $smsService = new \App\Services\SmsService();
            $smsSent = $smsService->sendOtp($proposal->client_phone, $otp);
            
            if (!$smsSent) {
                throw new \Exception('Failed to send SMS');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to registered phone number.',
                'debug_otp' => config('app.debug') ? $otp : null // Only show in debug mode
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send OTP for proposal {$proposal->proposal_no}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP and return proposal details
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'proposal_no' => 'required|string|max:50',
            'otp' => 'required|string|size:6'
        ]);

        $proposalNo = strtoupper(trim($request->proposal_no));
        $otp = trim($request->otp);
        
        $proposal = Proposal::where('proposal_no', $proposalNo)
            ->where('status', '!=', 'draft')
            ->first();

        if (!$proposal) {
            return response()->json([
                'success' => false,
                'message' => 'Proposal not found.'
            ], 404);
        }

        // Verify OTP
        if (!$proposal->otpIsValid($otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please try again.'
            ], 422);
        }

        // Clear OTP after successful verification
        $proposal->clearOtp();
        
        // Mark as verified if it was pending
        if ($proposal->status === 'pending') {
            $proposal->update(['status' => 'verified']);
        }

        // Return proposal details
        return response()->json([
            'success' => true,
            'message' => 'Proposal verified successfully!',
            'data' => [
                'proposal_no' => $proposal->proposal_no,
                'title' => $proposal->title,
                'amount' => $proposal->formatted_amount,
                'client_org' => $proposal->client_org ?? 'Not specified',
                'client_phone' => $proposal->client_phone,
                'status' => ucfirst($proposal->status),
                'verified_at' => now()->format('M j, Y \a\t g:i A')
            ]
        ]);
    }
}