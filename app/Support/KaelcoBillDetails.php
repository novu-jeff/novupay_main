<?php

namespace App\Support;

class KaelcoBillDetails
{
    private const MONTH_NAMES = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    private const MONTH_NUMBERS = [
        'jan' => '01', 'january' => '01',
        'feb' => '02', 'february' => '02',
        'mar' => '03', 'march' => '03',
        'apr' => '04', 'april' => '04',
        'may' => '05',
        'jun' => '06', 'june' => '06',
        'jul' => '07', 'july' => '07',
        'aug' => '08', 'august' => '08',
        'sep' => '09', 'sept' => '09', 'september' => '09',
        'oct' => '10', 'october' => '10',
        'nov' => '11', 'november' => '11',
        'dec' => '12', 'december' => '12',
    ];

    /**
     * Format bill months for dashboard/export display.
     */
    public static function formatBillMonthDisplay($billMonth, $description = null): string
    {
        $codes = self::extractYyyymmList($billMonth);
        if (empty($codes)) {
            $codes = self::extractYyyymmList($description);
        }

        if (!empty($codes)) {
            rsort($codes, SORT_STRING);

            return implode(', ', array_map([self::class, 'formatYyyymm'], $codes));
        }

        if (is_string($billMonth)) {
            $raw = trim($billMonth);
            if ($raw !== '' && strcasecmp($raw, 'null') !== 0) {
                return $raw;
            }
        }

        return '-';
    }

    /**
     * Persistable bill_month value (comma-separated YYYYMM).
     */
    public static function resolveBillMonth($payloadBillMonth, $description = null): ?string
    {
        $codes = self::extractYyyymmList($payloadBillMonth);
        if (empty($codes)) {
            $codes = self::extractYyyymmList($description);
        }

        if (!empty($codes)) {
            rsort($codes, SORT_STRING);

            return implode(',', $codes);
        }

        if (is_array($payloadBillMonth) || is_object($payloadBillMonth)) {
            $encoded = json_encode($payloadBillMonth);

            return $encoded !== false && $encoded !== 'null' ? $encoded : null;
        }

        if (is_string($payloadBillMonth)) {
            $raw = trim($payloadBillMonth);
            if ($raw !== '' && strcasecmp($raw, 'null') !== 0) {
                return $raw;
            }
        }

        return null;
    }

    public static function resolveSurcharge($payloadSurcharge, $description = null): float
    {
        if (is_numeric($payloadSurcharge)) {
            $value = round((float) $payloadSurcharge, 2);
            if ($value > 0) {
                return $value;
            }
        }

        $decoded = self::decodeJsonValue($description);
        if (is_array($decoded) && isset($decoded['surcharge']) && is_numeric($decoded['surcharge'])) {
            return round((float) $decoded['surcharge'], 2);
        }

        return is_numeric($payloadSurcharge) ? round((float) $payloadSurcharge, 2) : 0.0;
    }

    /**
     * @return list<string> Unique YYYYMM codes
     */
    public static function extractYyyymmList($value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }

        if (is_int($value) || is_float($value)) {
            $asString = (string) (int) $value;
            if (preg_match('/^\d{6}$/', $asString)) {
                return [$asString];
            }

            return [];
        }

        if (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || strcasecmp($trimmed, 'null') === 0) {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                return self::extractYyyymmList($decoded);
            }

            if (str_contains($trimmed, ',')) {
                $months = [];
                foreach (explode(',', $trimmed) as $part) {
                    $months = array_merge($months, self::extractYyyymmList(trim($part)));
                }

                return array_values(array_unique($months));
            }

            $one = self::extractYyyymmFromPeriod($trimmed);

            return $one ? [$one] : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $items = $value;
        if (isset($value['payments']) && is_array($value['payments'])) {
            $items = $value['payments'];
        } elseif (isset($value['period'])) {
            $items = [$value];
        }

        $months = [];
        foreach ($items as $item) {
            if (is_array($item) && array_key_exists('period', $item)) {
                $period = $item['period'] ?? '';
                if (is_string($period) && stripos($period, 'Disconnection Fee') !== false) {
                    continue;
                }
                $months = array_merge($months, self::extractYyyymmList($period));
                continue;
            }

            $months = array_merge($months, self::extractYyyymmList($item));
        }

        return array_values(array_unique($months));
    }

    public static function extractYyyymmFromPeriod($period): ?string
    {
        if ($period === null || $period === '') {
            return null;
        }

        if (!is_string($period) && !is_numeric($period)) {
            return null;
        }

        $period = trim(preg_replace('/\s+/', ' ', (string) $period) ?? '');
        if ($period === '' || stripos($period, 'Disconnection Fee') !== false) {
            return null;
        }

        if (preg_match('/\(\s*(\d{6})\s*\)/', $period, $match)) {
            return $match[1];
        }

        if (preg_match('/^(\d{6})$/', $period, $match)) {
            return $match[1];
        }

        if (preg_match('/^(\d{4})-(\d{2})(?:-\d{2})?$/', $period, $match)) {
            return $match[1] . $match[2];
        }

        if (preg_match('/([A-Za-z]{3,9})[-\s]+(\d{4})/', $period, $match)) {
            $monthNum = self::MONTH_NUMBERS[strtolower($match[1])] ?? null;
            if ($monthNum) {
                return $match[2] . $monthNum;
            }
        }

        if (preg_match('/(?<!\d)(\d{6})(?!\d)/', $period, $match)) {
            $month = (int) substr($match[1], 4, 2);
            if ($month >= 1 && $month <= 12) {
                return $match[1];
            }
        }

        return null;
    }

    public static function formatYyyymm(string $yyyymm): string
    {
        if (!preg_match('/^(\d{4})(\d{2})$/', $yyyymm, $match)) {
            return $yyyymm;
        }

        $month = (int) $match[2];

        return (self::MONTH_NAMES[$month] ?? $match[2]) . ' ' . $match[1];
    }

    private static function decodeJsonValue($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
