<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Image;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\Block\CaptionedImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;

final class CaptionedImageConverterTest extends BlockConverterTestCase
{
    protected $class = Image::class;
    protected $viewModelClass = AssetViewerInlineSet::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedImageConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class)
        );
        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new AssetViewerInlineSet()));
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'alt' => 'Image 1',
                    'uri' => 'https://example.com/image1',
                    'title' => 'An image\'s title',
                ],
            ],
            'with paragraph caption' => [
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
            'with MathML caption' => [
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
