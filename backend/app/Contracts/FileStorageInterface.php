<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    public function store(UploadedFile $file, string $fileType): array;

    public function delete(string $filePath): bool;

    public function exists(string $filePath): bool;
}
