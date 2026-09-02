<?php

namespace App\Support;

/**
 * Final pass for Word HTML exports: tables, images, and optional MHTML packaging.
 */
class WordHtmlDocumentProcessor
{
    public function __construct(
        private WordHtmlTableFixer $tableFixer,
        private WordMhtmlDocumentBuilder $mhtmlBuilder,
    ) {}

    /**
     * @param  array<string, array{cid?:string,mime:string,binary:string}>  $embeddedResources
     */
    public function process(string $html, array $embeddedResources = []): string
    {
        $html = $this->tableFixer->apply($html);
        $html = $this->prepareImages($html);

        $resources = $embeddedResources;
        $index = count($resources);

        $html = preg_replace_callback(
            '/\bsrc="(data:[^"]+)"/i',
            function (array $matches) use (&$resources, &$index): string {
                $parsed = $this->parseDataUri($matches[1]);
                if ($parsed === null) {
                    return $matches[0];
                }

                $cid = 'resource-'.(++$index);
                $resources[$cid] = [
                    'cid' => $cid,
                    'mime' => $parsed['mime'],
                    'binary' => $parsed['binary'],
                ];

                return 'src="cid:'.$cid.'"';
            },
            $html
        ) ?? $html;

        if ($resources === []) {
            return $html;
        }

        return $this->mhtmlBuilder->build($html, $resources);
    }

    protected function prepareImages(string $html): string
    {
        return preg_replace_callback(
            '/<img\b([^>]*?)>/i',
            function (array $matches): string {
                $attrs = $matches[1];

                if (! preg_match('/\bsrc="([^"]+)"/i', $attrs, $srcMatch)) {
                    return $matches[0];
                }

                if (! preg_match('/\bwidth="(\d+)"/i', $attrs)) {
                    $dims = WordLogoDocument::fromDataUri($srcMatch[1]);
                    if ($dims !== null) {
                        $attrs .= ' width="'.$dims['width_px'].'" height="'.$dims['height_px'].'"';
                        $attrs .= ' style="width:'.$dims['width_pt'].'pt;height:'.$dims['height_pt'].'pt;"';
                    }
                }

                if (! str_contains($attrs, 'class=')) {
                    $attrs .= ' class="logo-large"';
                }

                return '<img'.$attrs.'>';
            },
            $html
        ) ?? $html;
    }

    /**
     * @return array{mime:string,binary:string}|null
     */
    protected function parseDataUri(string $dataUri): ?array
    {
        if (! preg_match('/^data:([^;]+);base64,(.+)$/s', $dataUri, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return null;
        }

        return [
            'mime' => $matches[1],
            'binary' => $binary,
        ];
    }
}
