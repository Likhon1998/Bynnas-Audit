<?php

namespace App\Support;

class WordLogoDocument
{
    private const MAX_WIDTH_MM = 62.0;

    private const MAX_HEIGHT_MM = 16.0;

    /**
     * @return array{
     *     cid: string,
     *     mime: string,
     *     binary: string,
     *     width_px: int,
     *     height_px: int,
     *     width_pt: float,
     *     height_pt: float
     * }|null
     */
    public static function fromDataUri(?string $dataUri, string $cid = 'audit-logo'): ?array
    {
        if ($dataUri === null || $dataUri === '') {
            return null;
        }

        if (! preg_match('/^data:([^;]+);base64,(.+)$/s', $dataUri, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return null;
        }

        $size = @getimagesizefromstring($binary);
        if ($size === false) {
            return null;
        }

        [$widthPx, $heightPx] = $size;
        if ($widthPx <= 0 || $heightPx <= 0) {
            return null;
        }

        $maxWidthPx = self::mmToPx(self::MAX_WIDTH_MM);
        $maxHeightPx = self::mmToPx(self::MAX_HEIGHT_MM);
        $scale = min($maxWidthPx / $widthPx, $maxHeightPx / $heightPx, 1.0);

        $widthPx = max(1, (int) round($widthPx * $scale));
        $heightPx = max(1, (int) round($heightPx * $scale));

        return [
            'cid' => $cid,
            'mime' => $matches[1],
            'binary' => $binary,
            'width_px' => $widthPx,
            'height_px' => $heightPx,
            'width_pt' => round(self::pxToPt($widthPx), 2),
            'height_pt' => round(self::pxToPt($heightPx), 2),
        ];
    }

    protected static function mmToPx(float $mm): float
    {
        return $mm / 25.4 * 96;
    }

    protected static function pxToPt(int|float $px): float
    {
        return $px * 72 / 96;
    }
}
