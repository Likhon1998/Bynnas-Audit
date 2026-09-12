<?php

namespace App\Support;

use App\Models\AuditIndicator;

/**
 * Catalog-style indicator codes: {series}-{n} in Bangla digits (e.g. ১০০০-১, ৯০০০-২).
 * Series ৯০০০ is reserved for ad-hoc headings created from an audit report.
 */
class AuditIndicatorCodes
{
    public const CUSTOM_SERIES = 9000;

    /**
     * Next unused custom code in catalog form: ৯০০০-১, ৯০০০-২, …
     */
    public static function nextCustomReportCode(): string
    {
        $max = 0;
        $prefixBn = BanglaNumerals::fromInt(self::CUSTOM_SERIES);
        $prefixLatin = (string) self::CUSTOM_SERIES;

        $codes = AuditIndicator::query()
            ->where(function ($q) use ($prefixBn, $prefixLatin) {
                $q->where('indicator_code', 'like', $prefixBn.'-%')
                    ->orWhere('indicator_code', 'like', $prefixLatin.'-%');
            })
            ->pluck('indicator_code');

        foreach ($codes as $code) {
            $code = trim((string) $code);
            if (! preg_match('/^(?:'.$prefixBn.'|'.$prefixLatin.')[\-‐‑‒–—](.+)$/u', $code, $m)) {
                continue;
            }
            $n = BanglaNumerals::toInt($m[1]);
            if ($n !== null) {
                $max = max($max, $n);
            }
        }

        do {
            $max++;
            $candidate = $prefixBn.'-'.BanglaNumerals::fromInt($max);
        } while (AuditIndicator::query()->where('indicator_code', $candidate)->exists());

        return $candidate;
    }

    public static function isCustomReportCode(string $code): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }

        if (str_starts_with($code, 'রিপোর্ট-')) {
            return true;
        }

        $prefixBn = BanglaNumerals::fromInt(self::CUSTOM_SERIES);

        return (bool) preg_match('/^(?:'.$prefixBn.'|'.self::CUSTOM_SERIES.')[\-‐‑‒–—]/u', $code);
    }
}
