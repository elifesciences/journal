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
        return self::calculate($image->getWidth(), $image->getHeight(), $image->getFocalPointY(), $image->getFocalPointY(), $requestedW, $requestedH);
    }

    public static function calculate(int $sourceW, int $sourceH, int $focalX, int $focalY, int $requestedW, int $requestedH) : string
    {
        $focalX = (int) ($sourceW * ($focalX / 100));
        $focalY = (int) ($sourceH * ($focalY / 100));

        $sourceRatio = $sourceW / $sourceH;
        $requestedRatio = $requestedW / $requestedH;

        if ($requestedRatio > 1) {
            $w = $sourceW;
            $h = (int) (($sourceW / $requestedW) * $requestedH);
        } else {
            $w = (int) (($sourceH / $requestedH) * $requestedW);
            $h = $sourceH;
        }

        $foundRatio = $w / $h;

        $x = $focalX - (int) ($w / 2);
        $y = $focalY - (int) ($h / 2);

        if ($x < 0) {
            $x = 0;
        } elseif (($x + $w) > $sourceW) {
            $x = $sourceW - $w;
        }

        if ($y < 0) {
            $y = 0;
        } elseif (($y + $h) > $sourceH) {
            $y = $sourceH - $h;
        }

        $result = "$x,$y,$w,$h";

        if ("0,0,$sourceW,$sourceH" === $result) {
            return 'full';
        }

        return $result;
    }
}
