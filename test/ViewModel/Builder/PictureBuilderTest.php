<?php

namespace test\eLife\Journal\ViewModel\Builder;

use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PictureBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_basic_image()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->assertEquals(
            new ViewModel\Picture(
                [],
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_an_image_with_types_and_sizes()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $builder = $builder
            ->addType('image/svg+xml')
            ->addType('image/png')
            ->addType('image/webp')
            ->addSize(200, 300, '(media)')
            ->addSize(100);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/svg+xml:200:300',
                        'type' => 'image/svg+xml',
                        'media' => '(media)',
                    ],
                    [
                        'srcset' => 'path:image/webp:200:300:2 2x, path:image/webp:200:300:1 1x',
                        'type' => 'image/webp',
                        'media' => '(media)',
                    ],
                    [
                        'srcset' => 'path:image/png:200:300:2 2x, path:image/png:200:300:1 1x',
                        'type' => 'image/png',
                        'media' => '(media)',
                    ],
                    [
                        'srcset' => 'path:image/svg+xml:100:',
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
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_creates_an_image_with_types_and_sizes_without_oversizing()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $builder = $builder
            ->setOriginalSize(150, 150)
            ->addType('image/png')
            ->addType('image/svg+xml')
            ->addSize(200, 300, '(media)')
            ->addSize(100);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/svg+xml:200:300',
                        'type' => 'image/svg+xml',
                        'media' => '(media)',
                    ],
                    [
                        'srcset' => 'path:image/svg+xml:100:',
                        'type' => 'image/svg+xml',
                    ],
                    [
                        'srcset' => 'path:image/png:100::1.5 1.5x, path:image/png:100::1 1x',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_handles_rounding_correctly()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $builder = $builder
            ->setOriginalSize(101, 101)
            ->addType('image/png')
            ->addSize(100)
            ->addSize(90);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/png:100::1',
                        'type' => 'image/png',
                    ],
                    [
                        'srcset' => 'path:image/png:90::1.1 1.1x, path:image/png:90::1 1x',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );

        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $builder = $builder
            ->setOriginalSize(1800, 507)
            ->addType('image/png')
            ->addSize(1023, 288)
            ->addSize(1114, 336);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/png:1023:288:1.7 1.7x, path:image/png:1023:288:1 1x',
                        'type' => 'image/png',
                    ],
                    [
                        'srcset' => 'path:image/png:1114:336:1.6 1.6x, path:image/png:1114:336:1 1x',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_might_not_produce_any_size_based_sources_due_to_the_original_size()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $builder = $builder
            ->setOriginalSize(1, 1)
            ->addType('image/png')
            ->addSize(200, 300, '(media)')
            ->addSize(100);

        $this->assertEquals(
            new ViewModel\Picture(
                [
                    [
                        'srcset' => 'path:image/png',
                        'type' => 'image/png',
                    ],
                ],
                new ViewModel\Image('path:', [], 'alt')
            ),
            $builder->build()
        );
    }

    /**
     * @test
     */
    public function it_rejects_unknown_media_types()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->expectException(InvalidArgumentException::class);

        $builder->addType('image/foo');
    }

    /**
     * @test
     */
    public function it_rejects_impossible_widths()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->expectException(InvalidArgumentException::class);

        $builder->addSize(0);
    }

    /**
     * @test
     */
    public function it_rejects_impossible_heights()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->expectException(InvalidArgumentException::class);

        $builder->addSize(100, 0);
    }

    /**
     * @test
     */
    public function it_rejects_impossible_original_widths()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->expectException(InvalidArgumentException::class);

        $builder->setOriginalSize(0, 1);
    }

    /**
     * @test
     */
    public function it_rejects_impossible_original_heights()
    {
        $builder = new PictureBuilder(function () {
            return 'path:'.implode(':', func_get_args());
        }, 'alt');

        $this->expectException(InvalidArgumentException::class);

        $builder->setOriginalSize(1, 0);
    }
}
