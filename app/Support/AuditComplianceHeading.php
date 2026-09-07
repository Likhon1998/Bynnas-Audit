<?php

namespace App\Support;

/**
 * Official compliance section heading layout (centered BN / EN / period / follow-up).
 */
class AuditComplianceHeading
{
    public const DEFAULT_BN = 'বিগত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদনের জবাবের কমপ্লায়েন্স';

    public const DEFAULT_EN = '(Compliance of Previous Internal Audit Report Reply)';

    /**
     * @param  array<string, mixed>  $block
     * @return array{bn:string,en:string,period_line:string,followup_line:string}
     */
    public static function lines(array $block): array
    {
        $serial = trim((string) ($block['serial'] ?? ''));
        $title = trim((string) ($block['title'] ?? ''));
        $titleEn = trim((string) ($block['title_en'] ?? ''));

        if ($titleEn === '' && preg_match('/^(.*?)\s*(\([^)]*\))\s*$/u', $title, $m)) {
            $title = trim($m[1]);
            $titleEn = trim($m[2]);
        }

        if ($title === '') {
            $title = ($serial !== '' ? $serial.' ' : '').self::DEFAULT_BN;
        } elseif ($serial !== '' && ! preg_match('/^'.preg_quote($serial, '/').'\b/u', $title)) {
            $title = $serial.' '.$title;
        }

        if ($titleEn === '') {
            $titleEn = self::DEFAULT_EN;
        } elseif (! str_starts_with($titleEn, '(')) {
            $titleEn = '('.$titleEn.')';
        }

        $period = trim((string) ($block['period'] ?? ''));
        $followup = trim((string) ($block['followup_date'] ?? ''));

        return [
            'bn' => $title,
            'en' => $titleEn,
            'period_line' => 'নিরীক্ষাকাল ঃ '.($period !== '' ? $period : '………………'),
            'followup_line' => 'ফলোআপের তারিখ ঃ '.($followup !== '' ? $followup : '………………'),
        ];
    }
}
