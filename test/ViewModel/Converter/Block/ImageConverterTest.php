<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Image;
use eLife\Journal\ViewModel\CaptionlessImage;
use eLife\Journal\ViewModel\Converter\Block\ImageConverter;

final class ImageConverterTest extends BlockConverterTestCase
{
    protected $class = Image::class;
    protected $viewModelClass = CaptionlessImage::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImageConverter();
    }

    public function blocks()
    {
        return [
            [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                ],
            ],
        ];
    }
}
