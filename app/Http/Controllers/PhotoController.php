<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /** Only these public-disk folders may be served. */
    public const FOLDERS = ['shakha-employees/', 'organogram-employees/', 'audit-logos/', 'review-snapshots/'];

    public function show(string $path): StreamedResponse
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if(str_contains($path, '..'), 404);
        abort_unless(collect(self::FOLDERS)->contains(fn (string $folder) => str_starts_with($path, $folder)), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
