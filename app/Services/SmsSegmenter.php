<?php

namespace App\Services;

/**
 * SMS segmentation and encoding detection utility.
 * - GSM-7 single part: 160 chars; concatenated: 153 chars per part
 * - Unicode (UCS-2) single part: 70 chars; concatenated: 67 chars per part
 */
class SmsSegmenter
{
    /**
     * Determine if text is GSM-7 encodable.
     */
    public static function isGsm(string $text): bool
    {
        // Heuristic: treat as GSM-7 if string contains only ASCII (no multibyte chars)
        // This covers most practical cases and avoids complex extension table logic.
        return !preg_match('/[^\x00-\x7F]/u', $text);
    }

    /**
     * Compute segments information for given message.
     * Returns array: encoding, per_part_limit, concat_part_limit, segments, length, remaining_in_part
     */
    public static function segmentInfo(string $message): array
    {
        $isGsm = self::isGsm($message);
        $encoding = $isGsm ? 'GSM-7' : 'UCS-2';
        $len = $isGsm ? self::gsmLength($message) : mb_strlen($message, 'UTF-8');

        $singleLimit = $isGsm ? 160 : 70;
        $concatLimit = $isGsm ? 153 : 67;

        if ($len <= $singleLimit) {
            $segments = 1;
            $remaining = $singleLimit - $len;
        } else {
            $segments = (int) ceil($len / $concatLimit);
            $lastLen = $len % $concatLimit;
            $remaining = $lastLen === 0 ? 0 : ($concatLimit - $lastLen);
        }

        return [
            'encoding' => $encoding,
            'per_part_limit' => $singleLimit,
            'concat_part_limit' => $concatLimit,
            'segments' => $segments,
            'length' => $len,
            'remaining_in_part' => $remaining,
        ];
    }

    /**
     * Calculate character length for GSM-7 (treating basic table as length 1).
     */
    private static function gsmLength(string $message): int
    {
        return mb_strlen($message, 'UTF-8');
    }
}


