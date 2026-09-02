<?php

namespace App\Support;

/**
 * Package Word HTML as MHTML so embedded logos/images open reliably in Microsoft Word.
 */
class WordMhtmlDocumentBuilder
{
    /**
     * @param  array<string, array{cid?:string,mime:string,binary:string}>  $resources
     */
    public function build(string $html, array $resources): string
    {
        $boundary = '----=_BynnasAudit_'.bin2hex(random_bytes(8));
        $subject = 'Audit Report';

        $output = "MIME-Version: 1.0\r\n";
        $output .= 'Subject: '.$subject."\r\n";
        $output .= 'Content-Type: multipart/related; type="text/html"; boundary="'.$boundary."\"\r\n\r\n";

        $output .= '--'.$boundary."\r\n";
        $output .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $output .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $output .= "Content-Location: file:///C:/BynnasAudit/audit-report.doc\r\n\r\n";
        $output .= quoted_printable_encode($html)."\r\n\r\n";

        foreach ($resources as $key => $resource) {
            $cid = $resource['cid'] ?? (string) $key;
            $mime = $resource['mime'] ?: 'application/octet-stream';
            $binary = $resource['binary'] ?? '';

            $output .= '--'.$boundary."\r\n";
            $output .= 'Content-Type: '.$mime."\r\n";
            $output .= "Content-Transfer-Encoding: base64\r\n";
            $output .= 'Content-ID: <'.$cid.'>'."\r\n";
            $output .= 'Content-Location: '.$cid."\r\n\r\n";
            $output .= chunk_split(base64_encode($binary))."\r\n";
        }

        $output .= '--'.$boundary."--\r\n";

        return $output;
    }
}
