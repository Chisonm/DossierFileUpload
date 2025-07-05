<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface FileValidatorInterface
{
    public function validateFileType(string $fileType): void;

    public function validateFile(UploadedFile $file): void;
}
