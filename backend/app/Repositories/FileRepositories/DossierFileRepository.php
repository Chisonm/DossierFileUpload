<?php

namespace App\Repositories\FileRepositories;

use App\Contracts\DossierFileRepositoryInterface;
use App\Models\DossierFile;

class DossierFileRepository implements DossierFileRepositoryInterface
{
    public function create(array $data): DossierFile
    {
        return DossierFile::create($data);
    }

    public function findOrFail(int $id): DossierFile
    {
        return DossierFile::findOrFail($id);
    }

    public function delete(int $id): bool
    {
        return DossierFile::findOrFail($id)->delete();
    }

    public function getFilesGroupedByType(): array
    {
        $files = DossierFile::all();

        $groupedFiles = [];
        foreach (DossierFile::FILE_TYPES as $type) {
            $groupedFiles[$type] = $files->where('file_type', $type)->values();
        }

        return $groupedFiles;
    }
}
