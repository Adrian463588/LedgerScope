<?php

declare(strict_types=1);

namespace App\Services\Auth;

final class TotpService
{
    /**
     * Generate a new random Base32 secret key (16 characters / 80 bits).
     */
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Get the standard OTP Auth URL for scanning via authenticator apps.
     */
    public function getQrCodeUrl(string $email, string $secret): string
    {
        $issuer = rawurlencode('LedgerScope');
        $label = rawurlencode($email);
        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Verify a 6-digit TOTP code against a Base32 secret.
     */
    public function verify(string $secret, string $code): bool
    {
        // Clean the input code
        $code = (string) preg_replace('/[^0-9]/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }

        try {
            $key = $this->base32Decode($secret);
        } catch (\Exception) {
            return false;
        }

        // Standard 30-second time slice
        $timeSlice = floor(time() / 30);

        // Allow time drift: +/- 1 time slice (30 seconds)
        for ($i = -1; $i <= 1; $i++) {
            $slice = (float) ($timeSlice + $i);
            if ($this->calculateOtp($key, $slice) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate TOTP code for a specific time slice.
     */
    private function calculateOtp(string $key, float $slice): string
    {
        // Pack time slice as a 64-bit integer (big-endian)
        $time = pack('N*', 0) . pack('N*', (int) $slice);

        // HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $key, true);

        // Dynamic truncation
        $offset = ord($hmac[19]) & 0xf;
        $otpPart = (
            (ord($hmac[$offset]) & 0x7f) << 24 |
            (ord($hmac[$offset + 1]) & 0xff) << 16 |
            (ord($hmac[$offset + 2]) & 0xff) << 8 |
            (ord($hmac[$offset + 3]) & 0xff)
        );

        $otp = $otpPart % 1000000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32 string into raw bytes.
     */
    private function base32Decode(string $secret): string
    {
        $secret = strtoupper($secret);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        // Strip invalid characters
        $secret = preg_replace('/[^A-Z2-7]/', '', $secret);

        if (empty($secret)) {
            return '';
        }

        $buffer = '';
        $val = 0;
        $bits = 0;
        $len = strlen($secret);

        for ($i = 0; $i < $len; $i++) {
            $char = $secret[$i];
            $val = ($val << 5) | strpos($alphabet, $char);
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $buffer .= chr(($val >> $bits) & 0xff);
            }
        }

        return $buffer;
    }
}
