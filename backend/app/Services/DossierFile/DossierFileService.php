<?php

namespace App\Services\DossierFile;

use App\Contracts\DossierFileRepositoryInterface;
use App\Contracts\FileStorageInterface;
use App\Contracts\FileValidatorInterface;
use App\Exceptions\Files\InvalidFileTypeException;
use App\Models\DossierFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service class for handling dossier file operations.
 *
 * This service follows SOLID principles and delegates specific responsibilities
 * to specialized classes for better maintainability and testability.
 */
class DossierFileService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private FileStorageInterface $fileStorage,
        private FileValidatorInterface $fileValidator,
        private DossierFileRepositoryInterface $repository
    ) {}

    /**
     * Store a new dossier file.
     *
     * @param  UploadedFile  $file  The uploaded file instance
     * @param  string  $fileType  The category of the file
     * @return DossierFile The created dossier file model instance
     *
     * @throws InvalidFileTypeException If the file type is not valid
     */
    public function storeFile(UploadedFile $file, string $fileType): DossierFile
    {
        $this->fileValidator->validateFileType($fileType);
        $this->fileValidator->validateFile($file);

        $fileData = $this->fileStorage->store($file, $fileType);

        return $this->repository->create($fileData);
    }

    /**
     * Get all dossier files grouped by file type.
     *
     * @return array<string, \Illuminate\Support\Collection>
     */
    public function getFilesGroupedByType(): array
    {
        return $this->repository->getFilesGroupedByType();
    }

    /**
     * Delete a dossier file.
     *
     * @param  int  $id  The ID of the dossier file to delete
     * @return bool True if deletion was successful
     */
    public function deleteFile(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $file = $this->repository->findOrFail($id);

            $this->fileStorage->delete($file->file_path);

            return $this->repository->delete($id);
        });
    }
}
