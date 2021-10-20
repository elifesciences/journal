<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Model\Image;
use InvalidArgumentException;
use function min;

trait CreatesIiifUri
{
    final protected function iiifUri(Image $image, int $width = null, int $height = null, string $extension = null) : string
    {
        $uri = $image->getUri();
        $region = 'full';
        $size = 'full';

        if (($width && $width > ($image->getWidth() * 2)) || ($height && $height > ($image->getHeight() * 2))) {
            throw new InvalidArgumentException("Unable to scale the image $uri that large (requested width: $width, requested height: $height; actual width: {$image->getWidth()}, actual height {$image->getHeight()})");
        }

        if ($width && $height) {
            if (($width / $height) !== ($image->getWidth() / $image->getHeight())) {
                $region = IiifRegionCalculator::calculateForImage($image, $width, $height);

                if (!$this->dimensionNeeded($image->getHeight(), $image->getWidth(), $height, $width)) {
                    $height = null;
                }

                if (!$this->dimensionNeeded($image->getWidth(), $image->getHeight(), $width, $height)) {
                    $width = null;
                }

                $size = "$width,$height";
                if (',' === $size) {
                    $size = 'full';
                }
            } elseif ($width !== $image->getWidth()) {
                $size = "$width,";
            } elseif ($height !== $image->getHeight()) {
                $size = ",$height";
            }
        } elseif ($width && $width !== $image->getWidth()) {
            $size = "$width,";
        } elseif ($height && $height !== $image->getHeight()) {
            $size = ",$height";
        }

        if (empty($extension)) {
            $mediaType = explode(';', $image->getSource()->getMediaType(), 2);
            switch ($mediaType[0]) {
                case 'image/png':
                    $extension = 'png';
                    break;
                default:
                    $extension = 'jpg';
            }
        }

        return "$uri/$region/$size/0/default.$extension";
    }

    final protected function determineSizes(int $width, int $height, int $maxSize = 2000) : array
    {
        if ($width > $height) {
            $min = min($width, $maxSize);

            return [
                $min,
                (int) ($min * ($height / $width)),
            ];
        } else {
            $min = min($height, $maxSize);

            return [
                (int) ($min * ($width / $height)),
                $min,
            ];
        }
    }

    private function dimensionNeeded(int $originalOne, int $originalTwo, int $one = null, int $two = null) : bool
    {
        if ($one === $originalOne) {
            return false;
        }

        if ($two && $one === (int) ($two * ($originalOne / $originalTwo))) {
            return false;
        }

        return true;
    }
}
