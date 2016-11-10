<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\CaptionedImageConverter;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedImageConverterTest extends BlockConverterTestCase
{
    protected $class = 'eLife\ApiSdk\Model\Block\Image';
    protected $viewModelClass = CaptionedAsset::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedImageConverter();
    }

    public function blocks()
    {
        return [
            [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                    'title' => 'An image\'s caption',
                ],
            ],
        ];
    }
}
