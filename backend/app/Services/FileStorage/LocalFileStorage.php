<?php

namespace App\Services\FileStorage;

use App\Contracts\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalFileStorage implements FileStorageInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private string $disk = 'public',
        private string $baseDirectory = 'dossier-files'
    ) {}

    public function store(UploadedFile $file, string $fileType): array
    {
        $filename = $this->generateUniqueFilename($file);
        $directory = "{$this->baseDirectory}/{$fileType}";

        $path = $file->storeAs($directory, $filename, $this->disk);

        return [
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_type' => $fileType,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function delete(string $filePath): bool
    {
        if ($this->exists($filePath)) {
            return Storage::disk($this->disk)->delete($filePath);
        }

        return true;
    }

    public function exists(string $filePath): bool
    {
        return Storage::disk($this->disk)->exists($filePath);
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        return Str::uuid().'.'.$file->getClientOriginalExtension();
    }
}
