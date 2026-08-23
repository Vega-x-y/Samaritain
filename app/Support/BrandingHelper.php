<?php

namespace App\Support;

class BrandingHelper
{
    /**
     * Obtenir les images de branding encodées en base64.
     */
    public static function getEncodedImages(): array
    {
        $logoPath = public_path(config('branding.images.logo'));
        $wavePath = public_path(config('branding.images.wave'));

        return [
            'logoBase64' => file_exists($logoPath)
                ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
                : null,
            'waveBase64' => file_exists($wavePath)
                ? 'data:image/png;base64,'.base64_encode(file_get_contents($wavePath))
                : null,
        ];
    }

    /**
     * Obtenir la couleur principale pour un type de document.
     */
    public static function getColorForDocument(string $type): string
    {
        $colorKey = config("branding.document_colors.{$type}", 'primary');

        return config("branding.colors.{$colorKey}", config('branding.colors.primary'));
    }

    /**
     * Obtenir la couleur de fond pour un type de document.
     */
    public static function getBackgroundForDocument(string $type): string
    {
        $colorKey = config("branding.document_colors.{$type}", 'primary');

        return config("branding.backgrounds.{$colorKey}", config('branding.backgrounds.primary'));
    }

    /**
     * Obtenir toutes les données de branding pour un type de document.
     */
    public static function getDataForDocument(string $type): array
    {
        return array_merge(
            self::getEncodedImages(),
            [
                'accentColor' => self::getColorForDocument($type),
                'accentBgColor' => self::getBackgroundForDocument($type),
                'slogan' => config('branding.slogan'),
            ]
        );
    }
}
