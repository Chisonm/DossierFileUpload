<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * DossierFile Model
 *
 * Represents a file uploaded as part of a VISA dossier.
 * Files are categorized by type and can be retrieved, grouped, and deleted.
 */
class DossierFile extends Model
{
    use HasFactory;

    /**
     * File type constants
     */
    public const TYPE_PASSPORT = 'passport';

    public const TYPE_UTILITY_BILL = 'utility_bill';

    public const TYPE_OTHER = 'other';

    /**
     * Available file types
     */
    public const FILE_TYPES = [
        self::TYPE_PASSPORT,
        self::TYPE_UTILITY_BILL,
        self::TYPE_OTHER,
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'original_filename',
        'file_type',
        'file_path',
        'mime_type',
        'size',
    ];

    /**
     * Get the file's publicly accessible URL.
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Storage::url($this->file_path),
        );
    }

    /**
     * Get the file size in human-readable format.
     */
    protected function humanSize(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Number::fileSize($this->size),
        );
    }

    /**
     * Determine if the file is an image.
     */
    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => str_starts_with($this->mime_type, 'image/'),
        );
    }

    /**
     * Determine if the file is a PDF.
     */
    protected function isPdf(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->mime_type === 'application/pdf',
        );
    }

    /**
     * Get the file extension.
     */
    protected function fileExtension(): Attribute
    {
        return Attribute::make(
            get: fn (): string => pathinfo($this->original_filename, PATHINFO_EXTENSION),
        );
    }

    /**
     * Get the file's metadata.
     */
    public function getMetadata(): array
    {
        return [
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'file_type' => $this->file_type,
            'mime_type' => $this->mime_type,
            'extension' => $this->file_extension,
            'size' => $this->size,
            'human_size' => $this->human_size,
            'is_image' => $this->is_image,
            'is_pdf' => $this->is_pdf,
            'url' => $this->file_url,
            'uploaded_at' => $this->created_at,
        ];
    }
}
