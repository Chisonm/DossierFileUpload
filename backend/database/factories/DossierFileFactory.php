<?php

namespace Database\Factories;

use App\Models\DossierFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DossierFile>
 */
class DossierFileFactory extends Factory
{
    protected $model = DossierFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => $this->faker->uuid().'.pdf',
            'original_filename' => $this->faker->word().'.pdf',
            'file_type' => $this->faker->randomElement(DossierFile::FILE_TYPES),
            'file_path' => 'dossier-files/'.$this->faker->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }
}
