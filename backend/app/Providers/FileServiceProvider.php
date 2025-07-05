<?php

namespace App\Providers;

use App\Contracts\DossierFileRepositoryInterface;
use App\Contracts\FileStorageInterface;
use App\Contracts\FileValidatorInterface;
use App\Repositories\FileRepositories\DossierFileRepository;
use App\Services\FileStorage\LocalFileStorage;
use App\Services\Validation\DossierFileValidator;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FileStorageInterface::class, function ($app) {
            return new LocalFileStorage;
        });
        $this->app->singleton(FileValidatorInterface::class, DossierFileValidator::class);
        $this->app->singleton(DossierFileRepositoryInterface::class, DossierFileRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
