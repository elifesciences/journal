<?php

namespace test\eLife\Journal;

use PHPUnit_Framework_TestCase;
use Traversable;

class RegionTest extends PHPUnit_Framework_TestCase
{
    const TYPE_SQUARE = 0;
    const TYPE_LANDSCAPE = 1;
    const TYPE_PORTRAIT = 2;

    /**
     * @dataProvider provider
     */
    public function testFoo(int $sourceW, int $sourceH, int $focalX, int $focalY, int $requestedW, int $requestedH, string $expected)
    {
        $focalX = $sourceW * ($focalX / 100);
        $focalY = $sourceH * ($focalY / 100);

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

        $x = $focalX - ($w / 2);
        $y = $focalY - ($h / 2);

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

        /*$cropX = 0;
        $cropY = 0;

        if ($foundRatio > 1) {
            $cropY = $sourceW * $h / $w;
        } elseif ($foundRatio < 1) {
            $cropX = $sourceH * $w / $h;
        }

        $x = (int) ($cropX * $focalX);
        $y = (int) ($cropY * $focalY);*/

        $result = "$x,$y,$w,$h";

        if ("0,0,$sourceW,$sourceH" === $result) {
            $result = 'full';
        }

        $this->assertSame($expected, $result);
    }

    public function provider() : Traversable
    {
        yield [1280, 720, 50, 50, 720, 720, '280,0,720,720'];

        yield [800, 600, 50, 50, 400, 150, '0,150,800,300'];
//            yield [800,600,50,50,410,150,'0,146,800,292'];
        yield [500, 500, 50, 50, 500, 500, 'full'];
        yield [500, 500, 50, 50, 250, 250, 'full'];
        yield [500, 500, 50, 50, 500, 250, '0,125,500,250'];
        yield [500, 500, 25, 25, 500, 250, '0,0,500,250'];
        yield [500, 500, 75, 75, 500, 250, '0,250,500,250'];
        yield [500, 500, 0, 0, 500, 250, '0,0,500,250'];
        yield [500, 500, 5, 5, 500, 250, '0,0,500,250'];
        yield [500, 500, 40, 40, 500, 250, '0,75,500,250'];
        yield [500, 500, 95, 95, 500, 250, '0,250,500,250'];
        yield [500, 500, 100, 100, 500, 250, '0,250,500,250'];
        yield [500, 250, 50, 50, 500, 250, 'full'];
        yield [500, 250, 50, 50, 500, 100, '0,75,500,100'];
//        yield [500, 250, 50, 50, 250, 10, '0,75,500,20'];
//        yield [500, 250, 50, 50, 10, 250, '0,75,10,250'];
//        yield [500, 500, 50, 50, 10, 250, '0,75,20,500'];
    }
}
