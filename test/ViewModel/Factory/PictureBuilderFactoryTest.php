<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\ApiClient\HttpClient\ForbiddingHttpClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Image;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PictureBuilderFactoryTest extends TestCase
{
    /** @var DenormalizerInterface */
    private $denormalizer;

    /**
     * @before
     */
    public function setUpSerializer()
    {
        $apiSdk = new ApiSdk(new ForbiddingHttpClient());

        $this->denormalizer = $apiSdk->getSerializer();
    }

    /**
     * @test
     */
    public function it_creates_a_builder()
    {
        $factory = new PictureBuilderFactory();

        $builder = $factory->create(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'image/jpeg', null, null, 'alt');

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/webp:::1',
                        'type' => 'image/webp',
                    ],
                ],
                new ViewModel\Image('path:image/jpeg:::1', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_with_sizes()
    {
        $factory = new PictureBuilderFactory();

        $builder = $factory->create(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'image/jpeg', 100, 100);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/webp:100:100:2 2x, path:image/webp:100:100:1 1x',
                        'type' => 'image/webp',
                    ],
                    [
                        'srcset' => 'path:image/jpeg:100:100:2 2x, path:image/jpeg:100:100:1 1x',
                        'type' => 'image/jpeg',
                    ],
                ],
                new ViewModel\Image('path:image/jpeg:100:100:1')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_svgs()
    {
        $factory = new PictureBuilderFactory();

        $builder = $factory->create(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'image/svg+xml');

        $builder = $builder->addSize(100);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/svg+xml:100::1',
                        'type' => 'image/svg+xml',
                    ],
                    [
                        'srcset' => 'path:image/webp:100::2 2x, path:image/webp:100::1 1x',
                        'type' => 'image/webp',
                    ],
                    [
                        'srcset' => 'path:image/png:100::2 2x, path:image/png:100::1 1x',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('path:image/png:::1')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_images()
    {
        $factory = new PictureBuilderFactory();

        $image = $this->createImage([
            'uri' => 'https://example.com/image',
            'alt' => 'alt',
            'size' => [
                'width' => 200,
                'height' => 100,
            ],
            'source' => [
                'mediaType' => 'image/jpeg',
                'uri' => 'https://example.com/image.jpg',
                'filename' => 'Image.jpg',
            ],
        ]);

        $builder = $factory->forImage($image, 50);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.webp 2x, https://example.com/image/full/50,/0/default.webp 1x',
                        'type' => 'image/webp',
                    ],
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.jpg 2x, https://example.com/image/full/50,/0/default.jpg 1x',
                        'type' => 'image/jpeg',
                    ],
                ],
                new ViewModel\Image('https://example.com/image/full/50,/0/default.jpg', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_images_that_does_not_stretch_when_there_is_no_height()
    {
        $factory = new PictureBuilderFactory();

        $image = $this->createImage([
            'uri' => 'https://example.com/image',
            'alt' => 'alt',
            'size' => [
                'width' => 200,
                'height' => 100,
            ],
            'source' => [
                'mediaType' => 'image/jpeg',
                'uri' => 'https://example.com/image.jpg',
                'filename' => 'Image.jpg',
            ],
        ]);
        $builder = $factory->forImage($image, 1000);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'https://example.com/image/full/full/0/default.webp',
                        'type' => 'image/webp',
                    ],
                ],
                new ViewModel\Image('https://example.com/image/full/full/0/default.jpg', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_images_that_can_stretch_if_there_is_a_height()
    {
        $factory = new PictureBuilderFactory();

        $image = $this->createImage([
            'uri' => 'https://example.com/image',
            'alt' => 'alt',
            'size' => [
                'width' => 200,
                'height' => 100,
            ],
            'source' => [
                'mediaType' => 'image/jpeg',
                'uri' => 'https://example.com/image.jpg',
                'filename' => 'Image.jpg',
            ],
        ]);
        $builder = $factory->forImage($image, 400, 200);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'https://example.com/image/full/400,/0/default.webp',
                        'type' => 'image/webp',
                    ],
                ],
                new ViewModel\Image('https://example.com/image/full/400,/0/default.jpg', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_png_images()
    {
        $factory = new PictureBuilderFactory();

        $image = $this->createImage([
            'uri' => 'https://example.com/image',
            'alt' => 'alt',
            'size' => [
                'width' => 200,
                'height' => 100,
            ],
            'source' => [
                'mediaType' => 'image/png',
                'uri' => 'https://example.com/image.png',
                'filename' => 'Image.png',
            ],
        ]);

        $builder = $factory->forImage($image, 50);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.webp 2x, https://example.com/image/full/50,/0/default.webp 1x',
                        'type' => 'image/webp',
                    ],
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.png 2x, https://example.com/image/full/50,/0/default.png 1x',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('https://example.com/image/full/50,/0/default.png', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_builder_for_other_image_types()
    {
        $factory = new PictureBuilderFactory();

        $image = $this->createImage([
            'uri' => 'https://example.com/image',
            'alt' => 'alt',
            'size' => [
                'width' => 200,
                'height' => 100,
            ],
            'source' => [
                'mediaType' => 'image/tiff',
                'uri' => 'https://example.com/image.tif',
                'filename' => 'Image.tif',
            ],
        ]);

        $builder = $factory->forImage($image, 50);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.webp 2x, https://example.com/image/full/50,/0/default.webp 1x',
                        'type' => 'image/webp',
                    ],
                    [
                        'srcset' => 'https://example.com/image/full/100,/0/default.jpg 2x, https://example.com/image/full/50,/0/default.jpg 1x',
                        'type' => 'image/jpeg',
                    ],
                ],
                new ViewModel\Image('https://example.com/image/full/50,/0/default.jpg', [], 'alt')
            ),
            $builder->build()
        );
    }

    private function createImage(array $image) : Image
    {
        return $this->denormalizer->denormalize($image, Image::class);
    }
}
