<?php

namespace test\eLife\Journal\Helper;

use eLife\ApiSdk\Model\Image;
use eLife\Journal\Helper\IiifRegionCalculator;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\ViewModel\Converter\SerializerAwareTestCase;
use Traversable;

final class IiifRegionCalculatorTest extends TestCase
{
    use SerializerAwareTestCase;

    /**
     * @test
     */
    public function it_calculates_regions_for_an_image()
    {
        $json = [
            'uri' => 'https://www.example.com/iiif/iden%2Ftifier',
            'alt' => '',
            'source' => [
                'mediaType' => 'image/jpeg',
                'uri' => 'https://www.example.com/image.jpg',
                'filename' => 'image.jpg',
            ],
            'size' => [
                'width' => 800,
                'height' => 600,
            ],
            'focalPoint' => [
                'x' => 0,
                'y' => 100,
            ],
        ];

        $image = $this->serializer->denormalize($json, Image::class);

        $this->assertSame('0,0,600,600', IiifRegionCalculator::calculateForImage($image, 200, 200));
    }

    /**
     * @test
     * @dataProvider regionProvider
     */
    public function it_calculates_regions(int $sourceW, int $sourceH, int $focalX, int $focalY, int $requestedW, int $requestedH, string $expected)
    {
        $this->assertSame($expected, IiifRegionCalculator::calculate($sourceW, $sourceH, $focalX, $focalY, $requestedW, $requestedH));
    }

    public function regionProvider() : Traversable
    {
        yield [1280, 720, 50, 50, 720, 720, '280,0,720,720'];
        yield [800, 600, 50, 50, 400, 150, '0,150,800,300'];
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
        yield [800, 600, 50, 50, 900, 450, '0,100,800,400'];
        yield [7016, 2082, 50, 50, 900, 450, '1426,0,4164,2082'];
        yield [7016, 2082, 0, 50, 900, 450, '0,0,4164,2082'];
        yield [7016, 2082, 100, 50, 900, 450, '2852,0,4164,2082'];
        yield [7016, 2082, 50, 50, 3509, 1041, 'full'];
    }
}
