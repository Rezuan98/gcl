<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Proposal extends Model
{
    protected $fillable = [
        'unique_token',
        'title',
        'company_name',
        'client_phone',
        'pdf_path',
        'notes',
        'status',
        'otp_code',
        'otp_expires_at',
        'verified_at',
        'verification_count',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_count' => 'integer',
    ];

    /**
     * Generate unique verification token
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('unique_token', $token)->exists());

        return $token;
    }

    /**
     * Get the verification URL for this proposal
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('proposal.verify', ['token' => $this->unique_token]);
    }

    /**
     * Get masked phone number
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (!$this->client_phone) {
            return 'N/A';
        }

        $phone = preg_replace('/[^\d]/', '', $this->client_phone);
        
        if (strlen($phone) >= 10) {
            $lastFour = substr($phone, -4);
            $masked = str_repeat('*', strlen($phone) - 4) . $lastFour;
            return $masked;
        }
        
        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 3);
    }

    /**
     * Generate OTP for verification
     */
    public function generateOtp(): string
    {
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function otpIsValid(string $otp): bool
    {
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }

        if ($this->otp_expires_at->isPast()) {
            return false;
        }

        return $this->otp_code === $otp;
    }

    /**
     * Clear OTP after successful verification
     */
    public function clearOtp(): void
    {
        $this->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Mark proposal as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verification_count' => $this->verification_count + 1,
        ]);
    }

    /**
     * Get PDF URL for download
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return Storage::url($this->pdf_path);
    }

    /**
     * Check if PDF exists
     */
    public function hasPdf(): bool
    {
        return $this->pdf_path && Storage::disk('public')->exists($this->pdf_path);
    }

    /**
     * Delete PDF file
     */
    public function deletePdf(): void
    {
        if ($this->pdf_path && Storage::disk('public')->exists($this->pdf_path)) {
            Storage::disk('public')->delete($this->pdf_path);
        }
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('client_phone', 'like', "%{$search}%")
              ->orWhere('unique_token', 'like', "%{$search}%");
        });
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Delete proposal and its PDF when model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($proposal) {
            $proposal->deletePdf();
        });
    }
}