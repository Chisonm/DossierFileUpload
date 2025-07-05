<?php

namespace Tests\Unit\Services;

use App\Contracts\DossierFileRepositoryInterface;
use App\Contracts\FileStorageInterface;
use App\Contracts\FileValidatorInterface;
use App\Models\DossierFile;
use App\Services\DossierFile\DossierFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DossierFileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DossierFileService $service;

    protected FileStorageInterface $mockStorage;

    protected FileValidatorInterface $mockValidator;

    protected DossierFileRepositoryInterface $mockRepository;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for dependencies
        $this->mockStorage = $this->createMock(FileStorageInterface::class);
        $this->mockValidator = $this->createMock(FileValidatorInterface::class);
        $this->mockRepository = $this->createMock(DossierFileRepositoryInterface::class);

        // Initialize service with mocked dependencies
        $this->service = new DossierFileService(
            $this->mockStorage,
            $this->mockValidator,
            $this->mockRepository
        );

        // Fake storage for testing
        Storage::fake('public');
    }

    /**
     * Test storing a valid file successfully.
     */
    public function test_store_file_with_valid_type(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.pdf', 1024); // 1MB PDF
        $fileType = DossierFile::TYPE_PASSPORT;
        $storedPath = 'dossier-files/passport/test.pdf';

        // Mock validator with correct method names
        $this->mockValidator
            ->expects($this->once())
            ->method('validateFileType')
            ->with($fileType);

        $this->mockValidator
            ->expects($this->once())
            ->method('validateFile')
            ->with($file);

        // Mock storage to return file information as array
        $this->mockStorage
            ->expects($this->once())
            ->method('store')
            ->with($file, $fileType)
            ->willReturn([
                'original_filename' => 'test.pdf',
                'file_path' => $storedPath,
                'file_type' => $fileType,
                'mime_type' => 'application/pdf',
                'size' => 1024 * 1024,
            ]);

        // Create expected DossierFile model
        $expectedFile = new DossierFile([
            'id' => 1,
            'original_filename' => 'test.pdf',
            'file_path' => $storedPath,
            'file_type' => $fileType,
            'mime_type' => 'application/pdf',
            'size' => 1024 * 1024,
        ]);

        // Mock repository to return the created file
        $this->mockRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedFile);

        // Act
        $result = $this->service->storeFile($file, $fileType);

        // Assert
        $this->assertInstanceOf(DossierFile::class, $result);
        $this->assertEquals('test.pdf', $result->original_filename);
        $this->assertEquals($fileType, $result->file_type);
        $this->assertEquals('application/pdf', $result->mime_type);
        $this->assertEquals(1024 * 1024, $result->size);
    }

    /**
     * Test storing a file with invalid file type throws exception.
     */
    public function test_store_file_with_invalid_type_throws_exception(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.pdf', 1024);
        $invalidType = 'invalid_type';

        // Mock validator to throw exception for invalid type
        $this->mockValidator
            ->expects($this->once())
            ->method('validateFileType')
            ->with($invalidType)
            ->willThrowException(new \InvalidArgumentException('Invalid file type. Allowed types: '.implode(', ', DossierFile::FILE_TYPES)));

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type. Allowed types: '.implode(', ', DossierFile::FILE_TYPES));

        $this->service->storeFile($file, $invalidType);
    }

    /**
     * Test getting files grouped by type.
     */
    public function test_get_files_grouped_by_type(): void
    {
        // Arrange - Create expected grouped files
        $passportFiles = [
            new DossierFile(['file_type' => DossierFile::TYPE_PASSPORT]),
            new DossierFile(['file_type' => DossierFile::TYPE_PASSPORT]),
        ];
        $utilityBillFiles = [
            new DossierFile(['file_type' => DossierFile::TYPE_UTILITY_BILL]),
        ];
        $otherFiles = [
            new DossierFile(['file_type' => DossierFile::TYPE_OTHER]),
        ];

        $expectedGroupedFiles = [
            DossierFile::TYPE_PASSPORT => $passportFiles,
            DossierFile::TYPE_UTILITY_BILL => $utilityBillFiles,
            DossierFile::TYPE_OTHER => $otherFiles,
        ];

        // Mock repository to return grouped files
        $this->mockRepository
            ->expects($this->once())
            ->method('getFilesGroupedByType')
            ->willReturn($expectedGroupedFiles);

        // Act
        $groupedFiles = $this->service->getFilesGroupedByType();

        // Assert
        $this->assertIsArray($groupedFiles);
        $this->assertArrayHasKey(DossierFile::TYPE_PASSPORT, $groupedFiles);
        $this->assertArrayHasKey(DossierFile::TYPE_UTILITY_BILL, $groupedFiles);
        $this->assertArrayHasKey(DossierFile::TYPE_OTHER, $groupedFiles);

        $this->assertCount(2, $groupedFiles[DossierFile::TYPE_PASSPORT]);
        $this->assertCount(1, $groupedFiles[DossierFile::TYPE_UTILITY_BILL]);
        $this->assertCount(1, $groupedFiles[DossierFile::TYPE_OTHER]);
    }

    /**
     * Test getting files when no files exist.
     */
    public function test_get_files_grouped_by_type_when_empty(): void
    {
        // Arrange - Create empty grouped files for all types
        $emptyGroupedFiles = [];
        foreach (DossierFile::FILE_TYPES as $type) {
            $emptyGroupedFiles[$type] = [];
        }

        // Mock repository to return empty grouped files
        $this->mockRepository
            ->expects($this->once())
            ->method('getFilesGroupedByType')
            ->willReturn($emptyGroupedFiles);

        // Act
        $groupedFiles = $this->service->getFilesGroupedByType();

        // Assert
        $this->assertIsArray($groupedFiles);
        foreach (DossierFile::FILE_TYPES as $type) {
            $this->assertArrayHasKey($type, $groupedFiles);
            $this->assertCount(0, $groupedFiles[$type]);
        }
    }

    /**
     * Test deleting a file successfully.
     */
    public function test_delete_file_successfully(): void
    {
        // Arrange
        $fileId = 1;
        $filePath = 'dossier-files/passport/test.pdf';

        $file = new DossierFile([
            'id' => $fileId,
            'file_path' => $filePath,
        ]);

        // Mock repository to return the file and then delete it
        $this->mockRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willReturn($file);

        $this->mockRepository
            ->expects($this->once())
            ->method('delete')
            ->with($fileId)
            ->willReturn(true);

        // Mock storage to successfully delete the file
        $this->mockStorage
            ->expects($this->once())
            ->method('delete')
            ->with($filePath)
            ->willReturn(true);

        // Act
        $result = $this->service->deleteFile($fileId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test deleting a file when physical file doesn't exist.
     */
    public function test_delete_file_when_physical_file_missing(): void
    {
        // Arrange
        $fileId = 1;
        $filePath = 'dossier-files/passport/missing.pdf';

        $file = new DossierFile([
            'id' => $fileId,
            'file_path' => $filePath,
        ]);

        // Mock repository to return the file and then delete it
        $this->mockRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willReturn($file);

        $this->mockRepository
            ->expects($this->once())
            ->method('delete')
            ->with($fileId)
            ->willReturn(true);

        // Mock storage to return false (file doesn't exist) but service should still succeed
        $this->mockStorage
            ->expects($this->once())
            ->method('delete')
            ->with($filePath)
            ->willReturn(false);

        // Act
        $result = $this->service->deleteFile($fileId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test deleting non-existent file throws exception.
     */
    public function test_delete_non_existent_file_throws_exception(): void
    {
        // Arrange
        $fileId = 999;

        // Mock repository to throw ModelNotFoundException
        $this->mockRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($fileId)
            ->willThrowException(new \Illuminate\Database\Eloquent\ModelNotFoundException);

        // Assert & Act
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->deleteFile($fileId);
    }
}
