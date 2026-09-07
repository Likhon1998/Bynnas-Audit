<?php

namespace App\Support;

final class AuditCopyRecipients
{
    /**
     * Default অনুলিপি distribution list for audit cover pages.
     *
     * @return list<string>
     */
    public static function defaults(): array
    {
        return [
            'নির্বাহী পরিচালক',
            'উপ-নির্বাহী পরিচালক',
            'পরিচালক ঋণ',
            'উপ-প্রধান ঋণ',
            'যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ',
            'ফোকাল পার্সন',
            'আঞ্চলিক ব্যবস্থাপক',
            'শাখা ব্যবস্থাপক',
            'অফিস কপি',
        ];
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    public static function normalize(mixed $raw): array
    {
        if (! is_array($raw) || $raw === []) {
            return self::defaults();
        }

        $items = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $item = $item['label'] ?? $item['name'] ?? $item['text'] ?? '';
            }
            $label = trim((string) $item);
            if ($label !== '') {
                $items[] = $label;
            }
        }

        return $items !== [] ? array_values($items) : self::defaults();
    }
}
