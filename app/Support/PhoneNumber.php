<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Sanitize a phone number by stripping all non-numeric characters.
     */
    public static function sanitize(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
    }

    /**
     * Ensure the phone number includes the country code without a leading plus.
     */
    public static function withCountryCode(string $phone, ?string $countryCode = null): string
    {
        $countryCode ??= config('sms.default_country_code', '254');
        $digits = self::sanitize($phone);

        if ($digits === '') {
            return $countryCode;
        }

        if (str_starts_with($digits, $countryCode)) {
            return $digits;
        }

        if (strlen($digits) > 10 && !str_starts_with($digits, $countryCode)) {
            // Assume the caller supplied an international number with a different country code.
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (preg_match('/^(7|1)\d{8}$/', $digits)) {
            return $countryCode . $digits;
        }

        return $countryCode . ltrim($digits, '0');
    }

    /**
     * Format a phone number to the E.164 representation (e.g. +254712345678).
     */
    public static function e164(string $phone, ?string $countryCode = null): string
    {
        return '+' . self::withCountryCode($phone, $countryCode);
    }
}


