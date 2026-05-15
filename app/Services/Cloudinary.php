<?php

namespace App\Services;

class Cloudinary
{
    public const FOLDER_PROFILE_PHOTOS = 'profile_photos';

    public const FOLDER_HERO_CAROUSEL = 'hero_carousel_image';

    /** Hero carousel delivery size (full-width banner). */
    public const HERO_WIDTH = 1920;

    public const HERO_HEIGHT = 1080;

    public static function upload($file, array $options = [])
    {
        $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
        $response = $cloudinary->uploadApi()->upload($file, $options);

        return new class($response) {
            private $response;

            public function __construct($response)
            {
                $this->response = $response;
            }

            public function getSecurePath()
            {
                return $this->response['secure_url'] ?? null;
            }

            public function getPublicId()
            {
                return $this->response['public_id'] ?? null;
            }
        };
    }

    /**
     * Upload a hero carousel image to Cloudinary (full resolution stored).
     */
    public static function uploadHero($file): string
    {
        $result = self::upload($file, [
            'folder' => self::FOLDER_HERO_CAROUSEL,
            'resource_type' => 'image',
            'quality' => 'auto:best',
            'fetch_format' => 'auto',
        ]);

        $url = $result->getSecurePath();
        if (!$url) {
            throw new \RuntimeException('Cloudinary did not return a secure URL.');
        }

        return $url;
    }

    /**
     * Build a high-quality delivery URL for hero backgrounds (fixes blurry upscaling).
     */
    public static function heroImageUrl(?string $url, ?int $width = null, ?int $height = null): ?string
    {
        if (empty($url)) {
            return null;
        }

        $width = $width ?? self::HERO_WIDTH;
        $height = $height ?? self::HERO_HEIGHT;
        $transform = "w_{$width},h_{$height},c_fill,q_auto:best,f_auto,dpr_auto";

        if (str_contains($url, 'res.cloudinary.com')) {
            if (str_contains($url, $transform)) {
                return $url;
            }

            return preg_replace('#/image/upload/#', "/image/upload/{$transform}/", $url, 1);
        }

        // Unsplash / external — request a large width when possible
        if (str_contains($url, 'images.unsplash.com') && !preg_match('/[?&]w=\d+/', $url)) {
            $sep = str_contains($url, '?') ? '&' : '?';

            return $url . $sep . 'w=2400&q=85&fit=crop';
        }

        return $url;
    }

    /**
     * Responsive srcset for hero images (1x + 2x).
     *
     * @return array{src: string|null, srcset: string|null}
     */
    public static function heroImageSrcset(?string $url): array
    {
        $src = self::heroImageUrl($url, self::HERO_WIDTH, self::HERO_HEIGHT);
        $src2x = self::heroImageUrl($url, (int) (self::HERO_WIDTH * 1.5), (int) (self::HERO_HEIGHT * 1.5));

        if (!$src) {
            return ['src' => null, 'srcset' => null];
        }

        $srcset = $src2x && $src2x !== $src
            ? "{$src} 1x, {$src2x} 2x"
            : null;

        return ['src' => $src, 'srcset' => $srcset];
    }
}
