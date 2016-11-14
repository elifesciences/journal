<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\CaptionedVideoConverter;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedVideoConverterTest extends BlockConverterTestCase
{
    protected $class = 'eLife\ApiSdk\Model\Block\Video';
    protected $viewModelClass = CaptionedAsset::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedVideoConverter();
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'title' => 'Video\'s caption',
                    'sources' => [
                        [
                            'mediaType' => 'video/ogg',
                            'uri' => 'https://example.com/video1',
                        ],
                    ],
                    'width' => 800,
                    'height' => 600,
                    'image' => 'https://example.com/video1-thumbnail',
                ],
            ],
            'with paragraph caption' => [
                [
                    'title' => 'Video\'s caption',
                    'sources' => [
                        [
                            'mediaType' => 'video/ogg',
                            'uri' => 'https://example.com/video1',
                        ],
                    ],
                    'width' => 800,
                    'height' => 600,
                    'image' => 'https://example.com/video1-thumbnail',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'A video\'s caption',
                        ],
                    ],
                ],
            ],
            'with MathML caption' => [
                [
                    'title' => 'Video\'s caption',
                    'sources' => [
                        [
                            'mediaType' => 'video/ogg',
                            'uri' => 'https://example.com/video1',
                        ],
                    ],
                    'width' => 800,
                    'height' => 600,
                    'image' => 'https://example.com/video1-thumbnail',
                    'caption' => [
                        [
                            'type' => 'mathml',
                            'mathml' => '<math>A video\'s caption</math>',
                        ],
                    ],
                ],
            ],
        ];
    }
}
