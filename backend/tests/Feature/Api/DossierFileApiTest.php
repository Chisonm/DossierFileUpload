<?php

namespace Tests\Feature\Api;

use App\Models\DossierFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for Dossier File API endpoints.
 *
 * Tests the complete API functionality including request validation,
 * response formats, and HTTP status codes.
 */
class DossierFileApiTest extends TestCase
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
     * Test listing dossier files grouped by type.
     */
    public function test_can_list_dossier_files_grouped_by_type(): void
    {
        // Arrange - Create files of different types
        DossierFile::factory()->create(['file_type' => 'passport']);
        DossierFile::factory()->create(['file_type' => 'passport']);
        DossierFile::factory()->create(['file_type' => 'utility_bill']);
        DossierFile::factory()->create(['file_type' => 'other']);

        // Act
        $response = $this->getJson('/api/dossier-files');

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'passport' => [
                        '*' => [
                            'id',
                            'filename',
                            'original_filename',
                            'file_type',
                            'file_url',
                            'mime_type',
                            'size',
                            'human_size',
                            'is_image',
                            'is_pdf',
                            'created_at',
                        ],
                    ],
                    'utility_bill',
                    'other',
                ],
                'message',
            ])
            ->assertJson([
                'message' => 'Dossier files retrieved successfully',
            ]);

        // Verify counts
        $data = $response->json('data');
        $this->assertCount(2, $data['passport']);
        $this->assertCount(1, $data['utility_bill']);
        $this->assertCount(1, $data['other']);
    }

    /**
     * Test listing when no files exist.
     */
    public function test_list_returns_empty_groups_when_no_files(): void
    {
        // Act
        $response = $this->getJson('/api/dossier-files');

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'passport' => [],
                    'utility_bill' => [],
                    'other' => [],
                ],
                'message' => 'Dossier files retrieved successfully',
            ]);
    }

    /**
     * Test uploading a valid PDF file.
     */
    public function test_can_upload_valid_pdf_file(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('passport.pdf', 2048, 'application/pdf');

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'passport',
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'filename',
                    'original_filename',
                    'file_type',
                    'file_url',
                    'mime_type',
                    'size',
                    'human_size',
                    'is_image',
                    'is_pdf',
                    'created_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'original_filename' => 'passport.pdf',
                    'file_type' => 'passport',
                    'mime_type' => 'application/pdf',
                    'is_pdf' => true,
                    'is_image' => false,
                ],
                'message' => 'File uploaded successfully',
            ]);

        // Verify database
        $this->assertDatabaseHas('dossier_files', [
            'original_filename' => 'passport.pdf',
            'file_type' => 'passport',
        ]);

        // Verify file storage
        $dossierFile = DossierFile::latest()->first();
        $this->assertNotNull($dossierFile);

        if ($dossierFile->file_path) {
            Storage::disk('public')->assertExists($dossierFile->file_path);
        }
    }

    /**
     * Test uploading a valid image file.
     */
    public function test_can_upload_valid_image_file(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('utility-bill.jpg', 600, 400)->size(1024);

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'utility_bill',
        ]);

        // Assert
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'original_filename' => 'utility-bill.jpg',
                    'file_type' => 'utility_bill',
                    'mime_type' => 'image/jpeg',
                    'is_pdf' => false,
                    'is_image' => true,
                ],
                'message' => 'File uploaded successfully',
            ]);
    }

    /**
     * Test validation error when file is missing.
     */
    public function test_upload_fails_when_file_missing(): void
    {
        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file_type' => 'passport',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Test validation error when file type is missing.
     */
    public function test_upload_fails_when_file_type_missing(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_type']);
    }

    /**
     * Test validation error for invalid file type.
     */
    public function test_upload_fails_with_invalid_file_type(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'invalid_type',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_type']);
    }

    /**
     * Test validation error for unsupported file format.
     */
    public function test_upload_fails_with_unsupported_file_format(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.txt', 1024, 'text/plain');

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'other',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Test validation error when file exceeds size limit.
     */
    public function test_upload_fails_when_file_too_large(): void
    {
        // Arrange - Create 5MB file (exceeds 4MB limit)
        $file = UploadedFile::fake()->create('large.pdf', 5120, 'application/pdf');

        // Act
        $response = $this->postJson('/api/dossier-files', [
            'file' => $file,
            'file_type' => 'other',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    /**
     * Test deleting a dossier file.
     */
    public function test_can_delete_dossier_file(): void
    {
        // Arrange
        $filePath = 'dossier-files/passport/test.pdf';
        Storage::disk('public')->put($filePath, 'test content');

        $file = DossierFile::factory()->create([
            'file_path' => $filePath,
        ]);

        // Act
        $response = $this->deleteJson("/api/dossier-files/{$file->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'message' => 'File deleted successfully',
            ]);

        // Verify deletion
        $this->assertDatabaseMissing('dossier_files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Test deleting non-existent file returns 404.
     */
    public function test_delete_non_existent_file_returns_404(): void
    {
        // Act
        $response = $this->deleteJson('/api/dossier-files/89');

        // Assert
        $response->assertNotFound()
            ->assertJson([
                'message' => 'File not found',
            ]);
    }

    /**
     * Test API returns proper CORS headers.
     */
    public function test_api_returns_cors_headers(): void
    {
        // Act
        $response = $this->getJson('/api/dossier-files');

        // Assert
        $response->assertHeader('Access-Control-Allow-Origin');
    }
}
