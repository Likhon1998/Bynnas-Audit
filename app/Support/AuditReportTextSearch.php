<?php

namespace App\Support;

/**
 * Deep text search across an audit report — counts every occurrence of a name/word.
 */
class AuditReportTextSearch
{
    /**
     * @param  array<string, mixed>  $corpus  locationKey => text
     * @return array{
     *   query:string,
     *   total:int,
     *   locations:int,
     *   hits:list<array{location:string,label:string,tab:string,anchor:string,count:int,snippet:string}>
     * }
     */
    public static function search(string $query, array $corpus, bool $wholeWord = false): array
    {
        $query = trim($query);
        if ($query === '' || mb_strlen($query) < 1) {
            return ['query' => $query, 'total' => 0, 'locations' => 0, 'hits' => []];
        }

        $hits = [];
        $total = 0;

        foreach ($corpus as $meta) {
            if (! is_array($meta)) {
                continue;
            }
            $text = (string) ($meta['text'] ?? '');
            if ($text === '') {
                continue;
            }
            $count = self::countOccurrences($text, $query, $wholeWord);
            if ($count < 1) {
                continue;
            }
            $total += $count;
            $hits[] = [
                'location' => (string) ($meta['location'] ?? ''),
                'label' => (string) ($meta['label'] ?? 'Match'),
                'tab' => (string) ($meta['tab'] ?? 'page4'),
                'anchor' => (string) ($meta['anchor'] ?? ''),
                'count' => $count,
                'snippet' => self::snippet($text, $query, $wholeWord),
            ];
        }

        usort($hits, function (array $a, array $b) {
            if ($a['count'] === $b['count']) {
                return strcmp($a['label'], $b['label']);
            }

            return $b['count'] <=> $a['count'];
        });

        return [
            'query' => $query,
            'total' => $total,
            'locations' => count($hits),
            'hits' => $hits,
        ];
    }

    public static function countOccurrences(string $haystack, string $needle, bool $wholeWord = false): int
    {
        $haystack = self::normalize($haystack);
        $needle = self::normalize($needle);
        if ($needle === '' || $haystack === '') {
            return 0;
        }

        if (! $wholeWord) {
            return mb_substr_count($haystack, $needle);
        }

        $pattern = '/(?<![\p{L}\p{N}_])'.preg_quote($needle, '/').'(?![\p{L}\p{N}_])/u';
        if (@preg_match_all($pattern, $haystack, $m) === false) {
            return mb_substr_count($haystack, $needle);
        }

        return count($m[0] ?? []);
    }

    public static function snippet(string $text, string $needle, bool $wholeWord = false, int $radius = 42): string
    {
        $plain = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
        $norm = self::normalize($plain);
        $n = self::normalize($needle);
        $pos = mb_stripos($norm, $n);
        if ($pos === false) {
            return mb_strlen($plain) > 90 ? mb_substr($plain, 0, 90).'…' : $plain;
        }

        $start = max(0, $pos - $radius);
        $len = mb_strlen($n) + ($radius * 2);
        $slice = mb_substr($plain, $start, $len);
        $prefix = $start > 0 ? '…' : '';
        $suffix = ($start + $len) < mb_strlen($plain) ? '…' : '';

        return $prefix.$slice.$suffix;
    }

    public static function normalize(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }
}
