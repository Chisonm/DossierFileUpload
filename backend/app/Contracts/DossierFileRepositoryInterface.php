<?php

namespace App\Contracts;

use App\Models\DossierFile;

interface DossierFileRepositoryInterface
{
    public function create(array $data): DossierFile;

    public function findOrFail(int $id): DossierFile;

    public function delete(int $id): bool;

    public function getFilesGroupedByType(): array;
}
