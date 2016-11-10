<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Image;
use eLife\Journal\ViewModel\Converter\Block\CaptionedImageConverter;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedImageConverterTest extends BlockConverterTestCase
{
    protected $class = Image::class;
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
                    'title' => 'An image\'s title',
                ],
            ],
            [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                    'title' => 'An image\'s title',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'An image\'s caption',
                        ],
                    ],
                ],
            ],
            [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                    'title' => 'An image\'s title',
                    'caption' => [
                        [
                            'type' => 'mathml',
                            'mathml' => '<math>An image\'s caption</math>',
                        ],
                    ],
                ],
            ],
        ];
    }
}
