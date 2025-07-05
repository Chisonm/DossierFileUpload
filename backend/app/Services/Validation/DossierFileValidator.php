<?php

namespace App\Services\Validation;

use App\Contracts\FileValidatorInterface;
use App\Exceptions\Files\InvalidFileTypeException;
use App\Models\DossierFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class DossierFileValidator implements FileValidatorInterface
{
    private const MAX_FILE_SIZE = 4 * 1024 * 1024; // 4MB (This could also come from settings or config)

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/pdf',
    ];

    public function validateFileType(string $fileType): void
    {
        if (! in_array($fileType, DossierFile::FILE_TYPES)) {
            throw new InvalidFileTypeException(
                'Invalid file type. Allowed types: '.implode(', ', DossierFile::FILE_TYPES)
            );
        }
    }

    public function validateFile(UploadedFile $file): void
    {
        $this->validateFileSize($file);
        $this->validateMimeType($file);
    }

    private function validateFileSize(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw ValidationException::withMessages([
                'file' => 'File size must not exceed '.(self::MAX_FILE_SIZE / 1024 / 1024).'MB',
            ]);
        }
    }

    private function validateMimeType(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw ValidationException::withMessages([
                'file' => 'Invalid file type. Allowed types: '.implode(', ', self::ALLOWED_MIME_TYPES),
            ]);
        }
    }
}
