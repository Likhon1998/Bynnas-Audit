<?php

namespace App\Support;

class BanglaNumerals
{
    /** @var array<string, string> */
    public const LATIN_TO_BANGLA = [
        '0' => '০',
        '1' => '১',
        '2' => '২',
        '3' => '৩',
        '4' => '৪',
        '5' => '৫',
        '6' => '৬',
        '7' => '৭',
        '8' => '৮',
        '9' => '৯',
    ];

    public static function fromLatin(int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return strtr((string) $value, self::LATIN_TO_BANGLA);
    }

    public static function fromInt(int $value): string
    {
        return self::fromLatin($value);
    }

    /**
     * Normalize Bangla/ASCII digit strings to a float (empty → null).
     */
    public static function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $latin = strtr($raw, array_flip(self::LATIN_TO_BANGLA));
        $latin = str_replace([',', ' ', '٫'], ['', '', '.'], $latin);
        $latin = preg_replace('/[^0-9.\-]/', '', $latin) ?? '';

        if ($latin === '' || $latin === '-' || $latin === '.') {
            return null;
        }

        return is_numeric($latin) ? (float) $latin : null;
    }

    public static function toInt(mixed $value): ?int
    {
        $float = self::toFloat($value);
        if ($float === null) {
            return null;
        }

        return (int) round($float);
    }

    public static function markup(?string $text, string $variant = 'default'): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if ($text === '—') {
            return e($text);
        }

        $class = 'bn-num bn-'.preg_replace('/[^a-z0-9-]/', '', $variant);

        return '<span class="'.e($class).'">'.e($text).'</span>';
    }

    /**
     * Wrap Bengali digit runs inside mixed text (e.g. percentages, dates).
     */
    public static function highlight(?string $text, string $variant = 'stat'): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $safeVariant = preg_replace('/[^a-z0-9-]/', '', $variant) ?: 'stat';

        return preg_replace_callback(
            '/[০-৯]+(?:[\.٫][০-৯]+)?%?/u',
            fn (array $matches): string => '<span class="bn-num bn-'.$safeVariant.'">'
                .htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8').'</span>',
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        ) ?? htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
