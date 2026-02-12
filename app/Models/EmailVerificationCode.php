<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailVerificationCode extends TenantModels
{
    protected $fillable = ['email', 'code', 'registration_data', 'expires_at', 'verified_at'];

    protected $casts = [
        'registration_data' => 'array',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a new verification code
     */
    public static function generateCode(string $email, array $registrationData): self
    {
        // Delete any existing codes for this email
        self::where('email', $email)->delete();

        return self::create([
            'email' => $email,
            'code' => self::generateRandomCode(),
            'registration_data' => $registrationData,
            'expires_at' => now()->addMinutes(15), // Code expires in 15 minutes
        ]);
    }

    /**
     * Generate a random 6-digit code
     */
    public static function generateRandomCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the code is already verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark the code as verified
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Verify a code for an email
     */
    public static function verifyCode(string $email, string $code): ?self
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->first();

        if (!$verification || $verification->isExpired()) {
            return null;
        }

        return $verification;
    }
}
