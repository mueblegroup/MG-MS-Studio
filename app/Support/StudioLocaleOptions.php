<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

class StudioLocaleOptions
{
    public static function timezones(): array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $options = [];

        foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC) as $identifier) {
            if (str_starts_with($identifier, 'Etc/') || in_array($identifier, ['Factory'], true)) {
                continue;
            }

            $timezone = new DateTimeZone($identifier);
            $offset = $timezone->getOffset($now);
            $sign = $offset >= 0 ? '+' : '-';
            $absolute = abs($offset);
            $hours = intdiv($absolute, 3600);
            $minutes = intdiv($absolute % 3600, 60);
            $gmt = sprintf('GMT%s%02d:%02d', $sign, $hours, $minutes);

            $options[$identifier] = sprintf('%s — %s', $identifier, $gmt);
        }

        uasort($options, function (string $left, string $right): int {
            preg_match('/GMT([+-])(\d{2}):(\d{2})/', $left, $leftParts);
            preg_match('/GMT([+-])(\d{2}):(\d{2})/', $right, $rightParts);

            $leftOffset = (($leftParts[1] ?? '+') === '-' ? -1 : 1) * (((int) ($leftParts[2] ?? 0) * 60) + (int) ($leftParts[3] ?? 0));
            $rightOffset = (($rightParts[1] ?? '+') === '-' ? -1 : 1) * (((int) ($rightParts[2] ?? 0) * 60) + (int) ($rightParts[3] ?? 0));

            return $leftOffset === $rightOffset ? strcmp($left, $right) : $leftOffset <=> $rightOffset;
        });

        return $options;
    }

    public static function currencies(): array
    {
        return [
            'MYR' => 'MYR — Malaysian Ringgit (RM)',
            'SGD' => 'SGD — Singapore Dollar (S$)',
            'USD' => 'USD — US Dollar ($)',
            'AUD' => 'AUD — Australian Dollar (A$)',
            'GBP' => 'GBP — British Pound (£)',
            'EUR' => 'EUR — Euro (€)',
            'JPY' => 'JPY — Japanese Yen (¥)',
            'NZD' => 'NZD — New Zealand Dollar (NZ$)',
            'CAD' => 'CAD — Canadian Dollar (C$)',
            'HKD' => 'HKD — Hong Kong Dollar (HK$)',
            'THB' => 'THB — Thai Baht (฿)',
            'IDR' => 'IDR — Indonesian Rupiah (Rp)',
            'PHP' => 'PHP — Philippine Peso (₱)',
            'INR' => 'INR — Indian Rupee (₹)',
            'CNY' => 'CNY — Chinese Yuan (¥)',
            'TWD' => 'TWD — New Taiwan Dollar (NT$)',
            'KRW' => 'KRW — South Korean Won (₩)',
            'AED' => 'AED — UAE Dirham',
            'SAR' => 'SAR — Saudi Riyal',
        ];
    }
}
