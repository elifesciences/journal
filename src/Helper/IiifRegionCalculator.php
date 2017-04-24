<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Model\Image;

final class IiifRegionCalculator
{
    private function __construct()
    {
    }

    public static function calculateForImage(Image $image, int $requestedW, int $requestedH) : string
    {
        return self::calculate($image->getWidth(), $image->getHeight(), $image->getFocalPointX(), $image->getFocalPointY(), $requestedW, $requestedH);
    }

    public static function calculate(int $sourceW, int $sourceH, int $focalX, int $focalY, int $requestedW, int $requestedH) : string
    {
        $ratioWidth = $sourceW / $requestedW;
        $ratioHeight = $sourceH / $requestedH;

        if ($ratioHeight < $ratioWidth) {
            list($x, $x2) = self::calculateCrop($sourceW, $requestedW, $focalX, $ratioHeight);
            $y = 0;
            $y2 = $sourceH;
        } else {
            list($y, $y2) = self::calculateCrop($sourceH, $requestedH, $focalY, $ratioWidth);
            $x = 0;
            $x2 = $sourceW;
        }

        $w = $x2 - $x;
        $h = $y2 - $y;

        $result = "$x,$y,$w,$h";

        if ("0,0,$sourceW,$sourceH" === $result) {
            return 'full';
        }

        return $result;
    }

    private static function calculateCrop(int $sourceSize, int $requestedSize, int $focalPercentage, float $ratio)
    {
        $cropSize = ceil($ratio * $requestedSize);
        $focalPoint = (int) ($sourceSize * ($focalPercentage / 100));
        $cropStart = $focalPoint - $cropSize / 2;
        $cropEnd = $cropStart + $cropSize;

        if ($cropStart < 0) {
            $cropEnd -= $cropStart;
            $cropStart = 0;
        } elseif ($cropEnd > $sourceSize) {
            $cropStart -= ($cropEnd - $sourceSize);
            $cropEnd = $sourceSize;
        }

        return [ceil($cropStart), ceil($cropEnd)];
    }
}
