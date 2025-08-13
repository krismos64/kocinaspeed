<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageOptimizerService
{
    private const MAX_WIDTH = 1200;
    private const MAX_HEIGHT = 800;
    private const JPEG_QUALITY = 85;
    private const WEBP_QUALITY = 85;

    public function optimizeUploadedFile(UploadedFile $file, string $targetPath): string
    {
        $info = getimagesize($file->getPathname());
        if (!$info) {
            throw new \InvalidArgumentException('Fichier image invalide');
        }

        $sourceImage = $this->createImageFromFile($file->getPathname(), $info[2]);
        $optimizedImage = $this->resizeAndOptimize($sourceImage, $info[0], $info[1]);
        
        $filename = $this->generateOptimizedFilename($file->getClientOriginalName());
        $fullPath = $targetPath . '/' . $filename;

        // Sauvegarder en JPEG optimisé
        imagejpeg($optimizedImage, $fullPath, self::JPEG_QUALITY);
        
        // Générer aussi une version WebP si supporté
        if (function_exists('imagewebp')) {
            $webpPath = str_replace('.jpg', '.webp', $fullPath);
            imagewebp($optimizedImage, $webpPath, self::WEBP_QUALITY);
        }

        imagedestroy($sourceImage);
        imagedestroy($optimizedImage);

        return $filename;
    }

    public function optimizeExistingFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $info = getimagesize($filePath);
        if (!$info) {
            return false;
        }

        $sourceImage = $this->createImageFromFile($filePath, $info[2]);
        $optimizedImage = $this->resizeAndOptimize($sourceImage, $info[0], $info[1]);

        // Créer une sauvegarde
        $backupPath = $filePath . '.backup';
        copy($filePath, $backupPath);

        // Sauvegarder l'image optimisée
        imagejpeg($optimizedImage, $filePath, self::JPEG_QUALITY);

        imagedestroy($sourceImage);
        imagedestroy($optimizedImage);

        return true;
    }

    private function createImageFromFile(string $path, int $imageType)
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($path);
            default:
                throw new \InvalidArgumentException('Type d\'image non supporté');
        }
    }

    private function resizeAndOptimize($sourceImage, int $originalWidth, int $originalHeight)
    {
        // Calculer les nouvelles dimensions
        $ratio = min(self::MAX_WIDTH / $originalWidth, self::MAX_HEIGHT / $originalHeight, 1);
        $newWidth = (int)($originalWidth * $ratio);
        $newHeight = (int)($originalHeight * $ratio);

        // Créer l'image redimensionnée
        $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Préserver la transparence pour PNG
        imagealphablending($optimizedImage, false);
        imagesavealpha($optimizedImage, true);
        
        // Redimensionner avec une bonne qualité
        imagecopyresampled(
            $optimizedImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        return $optimizedImage;
    }

    private function generateOptimizedFilename(string $originalName): string
    {
        $pathinfo = pathinfo($originalName);
        $basename = preg_replace('/[^a-zA-Z0-9-_]/', '', $pathinfo['filename']);
        return $basename . '_' . uniqid() . '.jpg';
    }

    public function getImageDimensions(string $path): ?array
    {
        $info = getimagesize($path);
        return $info ? ['width' => $info[0], 'height' => $info[1]] : null;
    }

    public function calculateCompressionSavings(string $originalPath, string $optimizedPath): array
    {
        $originalSize = filesize($originalPath);
        $optimizedSize = filesize($optimizedPath);
        $savings = $originalSize - $optimizedSize;
        $percentage = ($savings / $originalSize) * 100;

        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'savings_bytes' => $savings,
            'savings_percentage' => round($percentage, 1)
        ];
    }
}