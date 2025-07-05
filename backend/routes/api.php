<?php

use App\Http\Controllers\Api\DossierFile\DossierFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dossier File Routes
Route::controller(DossierFileController::class)->group(function () {
    Route::get('/dossier-files', 'index');
    Route::post('/dossier-files', 'store');
    Route::delete('/dossier-files/{id}', 'destroy');
});
