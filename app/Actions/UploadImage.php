<?php

namespace App\Actions;

use App\Models\Property;
use Illuminate\Database\Eloquent\Model;

class UploadImage
{
    public function handle(Property $property, array $files): void
    public function handle(Model $model, array $files, string $folder = 'images/properties'): void
    {
        $folder = 'images/properties';

        foreach ($files as $imageFile) {
            $extension = $imageFile->getClientOriginalExtension();
            $filename = uniqid('prop_', true).'.'.$extension;
            $filename = uniqid('img_', true).'.'.$extension;
            $path = $imageFile->storeAs($folder, $filename);

            $property->images()->create([
            $model->images()->create([
                'image_url' => $path,
                'cover_image' => false,
            ]);
        }
    }
}
