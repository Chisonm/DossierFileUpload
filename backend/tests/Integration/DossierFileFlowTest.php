<?php

namespace Tests\Integration;

use App\Models\DossierFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Integration tests for the complete dossier file flow.
 *
 * Tests the entire process from file upload to deletion,
 * ensuring all components work together correctly.
 */
class DossierFileFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Fake the storage for testing
        Storage::fake('public');
    }

    /**
     * Test the complete flow: upload, list, and delete a file.
     */
    public function test_complete_dossier_file_flow(): void
    {
        // Step 1: Upload a file
        $file = UploadedFile::fake()->create('passport.pdf', 1024, 'application/pdf');

        $uploadResponse = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'passport',
        ]);

        $uploadResponse->assertCreated();
        $fileId = $uploadResponse->json('data.id');
        $filePath = $uploadResponse->json('data.file_path');

        // Verify file was stored
        $this->assertDatabaseHas('dossier_files', [
            'id' => $fileId,
            'original_filename' => 'passport.pdf',
            'file_type' => 'passport',
        ]);

        // Step 2: List files and verify the uploaded file is included
        $listResponse = $this->getJson('/api/dossier-files');

        $listResponse->assertOk();
        $passportFiles = $listResponse->json('data.passport');
        $this->assertCount(1, $passportFiles);
        $this->assertEquals($fileId, $passportFiles[0]['id']);

        // Step 3: Delete the file
        $deleteResponse = $this->deleteJson("/api/dossier-files/{$fileId}");

        $deleteResponse->assertOk();

        // Verify file was deleted
        $this->assertDatabaseMissing('dossier_files', [
            'id' => $fileId,
        ]);

        // Step 4: List files again and verify the file is no longer included
        $finalListResponse = $this->getJson('/api/dossier-files');

        $finalListResponse->assertOk();
        $finalPassportFiles = $finalListResponse->json('data.passport');
        $this->assertCount(0, $finalPassportFiles);
    }

    /**
     * Test uploading multiple files of different types and listing them.
     */
    public function test_upload_multiple_files_and_list_by_type(): void
    {
        // Upload a passport file
        $passportFile = UploadedFile::fake()->create('passport.pdf', 1024, 'application/pdf');
        $this->postJson('/api/dossier-files', [
            'file' => $passportFile,
            'file_type' => 'passport',
        ])->assertCreated();

        // Upload a utility bill file
        $utilityBillFile = UploadedFile::fake()->image('utility-bill.jpg')->size(1024);
        $this->postJson('/api/dossier-files', [
            'file' => $utilityBillFile,
            'file_type' => 'utility_bill',
        ])->assertCreated();

        // Upload another file as "other" type
        $otherFile = UploadedFile::fake()->create('other-document.pdf', 1024, 'application/pdf');
        $this->postJson('/api/dossier-files', [
            'file' => $otherFile,
            'file_type' => 'other',
        ])->assertCreated();

        // List all files and verify grouping
        $listResponse = $this->getJson('/api/dossier-files');

        $listResponse->assertOk();
        $data = $listResponse->json('data');

        $this->assertCount(1, $data['passport']);
        $this->assertCount(1, $data['utility_bill']);
        $this->assertCount(1, $data['other']);

        // Verify file details
        $this->assertEquals('passport.pdf', $data['passport'][0]['original_filename']);
        $this->assertEquals('utility-bill.jpg', $data['utility_bill'][0]['original_filename']);
        $this->assertEquals('other-document.pdf', $data['other'][0]['original_filename']);
    }

    /**
     * Test error handling when trying to upload an invalid file.
     */
    public function test_error_handling_for_invalid_file(): void
    {
        // Try to upload a file with invalid type
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'invalid_type',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file_type']);

        // Verify no files were stored
        $this->assertEquals(0, DossierFile::count());

        // List files and verify empty groups
        $listResponse = $this->getJson('/api/dossier-files');

        $listResponse->assertOk();
        $data = $listResponse->json('data');

        $this->assertCount(0, $data['passport']);
        $this->assertCount(0, $data['utility_bill']);
        $this->assertCount(0, $data['other']);
    }

    /**
     * Test error handling when trying to delete a non-existent file.
     */
    public function test_error_handling_for_deleting_non_existent_file(): void
    {
        // Try to delete a non-existent file
        $response = $this->deleteJson('/api/dossier-files/999');

        $response->assertNotFound();
    }
}
