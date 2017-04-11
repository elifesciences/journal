<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Model\Image;

trait CreatesIiifUri
{
    final protected function iiifUri(Image $image, int $width = null, int $height = null, string $extension = null) : string
    {
        $uri = $image->getUri();
        $region = 'full';
        $size = 'full';
        if ($width && $height) {
            if (($width / $height) !== ($image->getWidth() / $image->getHeight())) {
                $region = IiifRegionCalculator::calculateForImage($image, $width, $height);
                $size = "$width,$height";
            } elseif ($width !== $image->getWidth()) {
                $size = "$width,";
            }
        } elseif ($width) {
            $size = "$width,";
        } else {
            $size = ",$height";
        }

        if (empty($extension)) {
            $mediaType = explode($image->getSource()->getMediaType(), ';', 2);
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
}
