<?php

namespace App\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait DownloadsImages
{
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    protected function downloadAndStoreImage(string $url, string $directory): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $image = $response->body();

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $extension = self::ALLOWED_IMAGE_MIMES[$finfo->buffer($image)] ?? null;
        if ($extension === null) {
            return null;
        }

        $filename = $directory . '/' . md5($image) . '.' . $extension;
        if (Storage::disk('public')->put($filename, $image)) {
            return $filename;
        }

        return null;
    }
}
