<?php

namespace App\Http\Controllers\Api\DossierFile;

use App\Exceptions\Files\InvalidFileTypeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDossierFileRequest;
use App\Http\Resources\DossierFileResource;
use App\Services\DossierFile\DossierFileService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DossierFileController extends Controller
{
    public function __construct(
        protected DossierFileService $dossierFileService
    ) {}

    /**
     * Display a listing of the dossier files.
     */
    public function index(): JsonResponse
    {
        $groupedFiles = $this->dossierFileService->getFilesGroupedByType();

        // Transform each file in each group using the resource
        $response = [];
        foreach ($groupedFiles as $type => $files) {
            $response[$type] = DossierFileResource::collection($files);
        }

        return response()->json([
            'data' => $response,
            'message' => 'Dossier files retrieved successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created dossier file.
     */
    public function store(StoreDossierFileRequest $request): JsonResponse
    {
        try {
            $file = $this->dossierFileService->storeFile(
                $request->file('file'),
                $request->input('file_type')
            );

            return response()->json([
                'data' => new DossierFileResource($file),
                'message' => 'File uploaded successfully',
            ], Response::HTTP_CREATED);
        } catch (InvalidFileTypeException $e) {
            Log::error('File upload failed: '.$e->getMessage());

            return response()->json([
                'message' => 'Invalid file type or format',
                'errors' => [
                    'file' => [$e->getMessage()],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('File upload failed: '.$e->getMessage());

            return response()->json([
                'message' => 'File upload failed. Please try again.',
                'errors' => [
                    'file' => ['An unexpected error occurred'],
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified dossier file.
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->dossierFileService->deleteFile($id);

            return response()->json([
                'message' => 'File deleted successfully',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('File not found: '.$e->getMessage());

            return response()->json([
                'message' => 'File not found',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
