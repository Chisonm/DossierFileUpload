<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DossierFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'file_type' => $this->file_type,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->human_size,
            'is_image' => $this->is_image,
            'is_pdf' => $this->is_pdf,
            'created_at' => $this->created_at,
        ];
    }
}
