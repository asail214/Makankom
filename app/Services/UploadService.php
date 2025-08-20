<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct(private string $disk = 'public')
    {
    }

    public function uploadFile(mixed $mediable, UploadedFile $file, string $directory, ?string $tag = null): array
    {
        $directory = trim($directory, '/');
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $fileName = $safeName.'-'.Str::random(6).'.'.$extension;
        $path = $file->storeAs($directory, $fileName, [
            'disk' => $this->disk,
            'visibility' => 'public',
        ]);

        $url = Storage::disk($this->disk)->url($path);

        $media = Media::create([
            'file_name' => $fileName,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'alt_text' => $tag,
            'caption' => $tag,
            'mediable_id' => $mediable->id,
            'mediable_type' => $mediable::class,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return ['media' => $media, 'url' => $url, 'path' => $path];
    }

    public function saveExternalUrl(mixed $mediable, string $externalUrl, string $tag, string $directory = ''): array
    {
        $fileName = basename(parse_url($externalUrl, PHP_URL_PATH) ?? 'remote');
        $media = Media::create([
            'file_name' => $fileName,
            'file_path' => $externalUrl,
            'file_type' => 'external_url',
            'file_size' => 0,
            'mime_type' => 'text/uri-list',
            'alt_text' => $tag,
            'caption' => $tag,
            'mediable_id' => $mediable->id,
            'mediable_type' => $mediable::class,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return ['media' => $media, 'url' => $externalUrl, 'path' => $externalUrl];
    }
}


