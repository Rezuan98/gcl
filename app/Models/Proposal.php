<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Proposal extends Model
{
    protected $fillable = [
        'proposal_no', 
        'title', 
        'amount',
        'currency_code',
        'client_org', 
         
        'client_email', 
        'client_phone', // Used for OTP verification
        'notes', 
        'status',
        'otp_code', 
        'otp_expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'otp_expires_at' => 'datetime',
    ];

    /**
     * Get masked phone number for security
     */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = $this->client_phone ?? '';
        if (strlen($phone) < 7) {
            return '***********';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        if (!$this->amount) {
            return 'Not specified';
        }

        $symbol = $this->currency_code === 'BDT' ? '৳' : $this->currency_code . ' ';
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'verified' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
            'pending' => 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200',
            'draft' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
            default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
        };
    }

    /**
     * Check if OTP is valid
     */
    public function otpIsValid(string $code): bool
    {
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }
        
        return $this->otp_code === $code && Carbon::now()->lt($this->otp_expires_at);
    }

    /**
     * Generate and save OTP for client_phone
     */
    public function generateOtp(): string
    {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5), // 5 minutes expiry
        ]);

        return $otp;
    }

    /**
     * Clear OTP data
     */
    public function clearOtp(): void
    {
        $this->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Get the phone number for OTP verification
     */
    public function getVerificationPhoneAttribute(): string
    {
        return $this->client_phone;
    }

    /**
     * Scope for published proposals (non-draft)
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', '!=', 'draft');
    }

    /**
     * Scope for draft proposals
     */
    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for verified proposals
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for pending proposals
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Search scope
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('proposal_no', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('client_org', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'proposal_no';
    }
}