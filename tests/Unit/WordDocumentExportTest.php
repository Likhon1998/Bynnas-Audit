<?php

namespace Tests\Unit;

use App\Support\WordLogoDocument;
use App\Support\WordMhtmlDocumentBuilder;
use PHPUnit\Framework\TestCase;

class WordDocumentExportTest extends TestCase
{
    public function test_logo_document_scales_to_cover_box(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNk+M9Qz0AEYBxVSF+FABJAD9/qQ8WCAAAAAElFTkSuQmCC', true);
        $dataUri = 'data:image/png;base64,'.base64_encode($png);

        $logo = WordLogoDocument::fromDataUri($dataUri);
        $this->assertNotNull($logo);
        $this->assertSame('audit-logo', $logo['cid']);
        $this->assertSame(10, $logo['width_px']);
        $this->assertSame(10, $logo['height_px']);
    }

    public function test_mhtml_builder_embeds_html_and_binary_parts(): void
    {
        $html = '<html><body><img src="cid:audit-logo" width="10" height="10"></body></html>';
        $mhtml = (new WordMhtmlDocumentBuilder)->build($html, [
            'audit-logo' => [
                'cid' => 'audit-logo',
                'mime' => 'image/png',
                'binary' => 'png-bytes',
            ],
        ]);

        $this->assertStringContainsString('multipart/related', $mhtml);
        $this->assertStringContainsString('Content-ID: <audit-logo>', $mhtml);
        $this->assertStringContainsString('quoted-printable', $mhtml);
    }
}
