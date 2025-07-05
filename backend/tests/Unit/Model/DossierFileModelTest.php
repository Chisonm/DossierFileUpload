<?php

namespace Tests\Unit\Models;

use App\Models\DossierFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for DossierFile model.
 *
 * Tests model attributes, accessors, and constants.
 */
class DossierFileModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test file type constants are defined correctly.
     */
    public function test_file_type_constants_are_defined(): void
    {
        $this->assertEquals('passport', DossierFile::TYPE_PASSPORT);
        $this->assertEquals('utility_bill', DossierFile::TYPE_UTILITY_BILL);
        $this->assertEquals('other', DossierFile::TYPE_OTHER);
    }

    /**
     * Test FILE_TYPES array contains all type constants.
     */
    public function test_file_types_array_contains_all_types(): void
    {
        $expectedTypes = [
            DossierFile::TYPE_PASSPORT,
            DossierFile::TYPE_UTILITY_BILL,
            DossierFile::TYPE_OTHER,
        ];

        $this->assertEquals($expectedTypes, DossierFile::FILE_TYPES);
    }

    /**
     * Test human_size accessor for various file sizes.
     */
    public function test_human_size_accessor_for_bytes(): void
    {
        $file = DossierFile::factory()->make(['size' => 500]);
        $this->assertEquals('500 B', $file->human_size);
    }

    /**
     * Test human_size accessor for kilobytes.
     */
    public function test_human_size_accessor_for_kilobytes(): void
    {
        $file = DossierFile::factory()->make(['size' => 2048]);
        $this->assertEquals('2 KB', $file->human_size);
    }

    /**
     * Test human_size accessor for megabytes.
     */
    public function test_human_size_accessor_for_megabytes(): void
    {
        $file = DossierFile::factory()->make(['size' => 3145728]); // 3 MB
        $this->assertEquals('3 MB', $file->human_size);
    }

    /**
     * Test human_size accessor for gigabytes.
     */
    public function test_human_size_accessor_for_gigabytes(): void
    {
        $file = DossierFile::factory()->make(['size' => 2147483648]); // 2 GB
        $this->assertEquals('2 GB', $file->human_size);
    }

    /**
     * Test is_image accessor for image files.
     */
    public function test_is_image_accessor_for_image_files(): void
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($imageTypes as $mimeType) {
            $file = DossierFile::factory()->make(['mime_type' => $mimeType]);
            $this->assertTrue($file->is_image, "Failed for mime type: {$mimeType}");
        }
    }

    /**
     * Test is_image accessor for non-image files.
     */
    public function test_is_image_accessor_for_non_image_files(): void
    {
        $nonImageTypes = ['application/pdf', 'text/plain', 'application/msword'];

        foreach ($nonImageTypes as $mimeType) {
            $file = DossierFile::factory()->make(['mime_type' => $mimeType]);
            $this->assertFalse($file->is_image, "Failed for mime type: {$mimeType}");
        }
    }

    /**
     * Test is_pdf accessor for PDF files.
     */
    public function test_is_pdf_accessor_for_pdf_files(): void
    {
        $file = DossierFile::factory()->make(['mime_type' => 'application/pdf']);
        $this->assertTrue($file->is_pdf);
    }

    /**
     * Test is_pdf accessor for non-PDF files.
     */
    public function test_is_pdf_accessor_for_non_pdf_files(): void
    {
        $nonPdfTypes = ['image/jpeg', 'text/plain', 'application/msword'];

        foreach ($nonPdfTypes as $mimeType) {
            $file = DossierFile::factory()->make(['mime_type' => $mimeType]);
            $this->assertFalse($file->is_pdf, "Failed for mime type: {$mimeType}");
        }
    }

    /**
     * Test file_url accessor generates correct URL.
     */
    public function test_file_url_accessor(): void
    {
        $file = DossierFile::factory()->make([
            'file_path' => 'dossier-files/passport/test-file.pdf',
        ]);

        $expectedUrl = Storage::url('dossier-files/passport/test-file.pdf');
        $this->assertEquals($expectedUrl, $file->file_url);
    }

    /**
     * Test model fillable attributes.
     */
    public function test_fillable_attributes(): void
    {
        $fillable = [
            'filename',
            'original_filename',
            'file_type',
            'file_path',
            'mime_type',
            'size',
        ];

        $file = new DossierFile;
        $this->assertEquals($fillable, $file->getFillable());
    }

    /**
     * Test model can be created with factory.
     */
    public function test_model_can_be_created_with_factory(): void
    {
        $file = DossierFile::factory()->create();

        $this->assertDatabaseHas('dossier_files', [
            'id' => $file->id,
            'filename' => $file->filename,
        ]);
    }

    /**
     * Test model timestamps are enabled.
     */
    public function test_timestamps_are_enabled(): void
    {
        $file = DossierFile::factory()->create();

        $this->assertNotNull($file->created_at);
        $this->assertNotNull($file->updated_at);
    }
}
