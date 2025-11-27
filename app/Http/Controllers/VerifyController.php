<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class VerifyController extends Controller
{
    /**
     * Show the public verification page for a specific proposal
     */
    public function show($token)
    {
        $proposal = Proposal::where('unique_token', $token)
            ->where('status', '!=', 'draft')
            ->first();

        if (!$proposal) {
            abort(404, 'Proposal not found or not available for verification.');
        }

        return view('verify.show', compact('proposal'));
    }

    /**
     * Send OTP to the proposal's registered phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $proposal = Proposal::where('unique_token', $request->token)
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
            $smsService = new SmsService();
            $smsSent = $smsService->sendOtp($proposal->client_phone, $otp);
            
            if (!$smsSent) {
                throw new \Exception('Failed to send SMS');
            }
            
            Log::info("OTP sent for proposal", [
                'token' => $proposal->unique_token,
                'phone' => $proposal->masked_phone
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to registered phone number.',
                'debug_otp' => config('app.debug') ? $otp : null // Only show in debug mode
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send OTP for proposal {$proposal->unique_token}: " . $e->getMessage());
            
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
            'token' => 'required|string',
            'otp' => 'required|string|size:6'
        ]);

        $otp = trim($request->otp);
        
        $proposal = Proposal::where('unique_token', $request->token)
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
        
        // Mark as verified
        $proposal->markAsVerified();

        Log::info("Proposal verified successfully", [
            'token' => $proposal->unique_token,
            'verification_count' => $proposal->verification_count
        ]);

        // Return proposal details with PDF download link
        return response()->json([
            'success' => true,
            'message' => 'Proposal verified successfully!',
            'data' => [
                'title' => $proposal->title,
                'company_name' => $proposal->company_name,
                'client_phone' => $proposal->client_phone,
                'has_pdf' => $proposal->hasPdf(),
                'pdf_url' => $proposal->pdf_url,
                'status' => ucfirst($proposal->status),
                'verified_at' => $proposal->verified_at->format('M j, Y \a\t g:i A'),
                'verification_count' => $proposal->verification_count
            ]
        ]);
    }

    /**
     * Download proposal PDF (after OTP verification)
     */
    public function downloadPdf($token)
    {
        $proposal = Proposal::where('unique_token', $token)
            ->where('status', 'verified')
            ->first();

        if (!$proposal) {
            abort(404, 'Proposal not found or not verified.');
        }

        if (!$proposal->hasPdf()) {
            abort(404, 'PDF file not found.');
        }

        $filename = $proposal->title . '.pdf';
        
        return response()->download(
            storage_path('app/public/' . $proposal->pdf_path),
            $filename
        );
    }
}