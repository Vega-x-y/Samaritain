<?php

namespace App\Actions;

use App\Models\Hotel;

class UploadHotelImage
{
    public function handle(Hotel $hotel, array $files): void
    {
        $folder = 'images/hotels';

        foreach ($files as $imageFile) {
            $extension = $imageFile->getClientOriginalExtension();
            $filename = uniqid('hotel_', true).'.'.$extension;
            $path = $imageFile->storeAs($folder, $filename);

            $hotel->images()->create([
                'image_url' => $path,
                'cover_image' => false,
            ]);
        }
    }
}
