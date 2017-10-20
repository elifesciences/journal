<?php

namespace test\eLife\Journal\ViewModel\Builder;

use eLife\Journal\ViewModel\Builder\PictureBuilder;
use eLife\Patterns\ViewModel;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

final class PictureBuilderTest extends PHPUnit_Framework_TestCase
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
                        'srcset' => 'path:image/png:400:600 400w, path:image/png:200:300 200w',
                        'type' => 'image/png',
                        'media' => '(media)',
                    ],
                    [
                        'srcset' => 'path:image/svg+xml:100:',
                        'type' => 'image/svg+xml',
                    ],
                    [
                        'srcset' => 'path:image/png:200:0 200w, path:image/png:100:0 100w',
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
}
