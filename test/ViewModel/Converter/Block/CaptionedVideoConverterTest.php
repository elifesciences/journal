<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\Helper\DownloadLinkUriGenerator;
use eLife\Journal\ViewModel\Converter\Block\CaptionedVideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\CaptionedAsset;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CaptionedVideoConverterTest extends BlockConverterTestCase
{
    protected $class = 'eLife\ApiSdk\Model\Block\Video';
    protected $viewModelClass = CaptionedAsset::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedVideoConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            new DownloadLinkUriGenerator($this->createMock(UrlGeneratorInterface::class), new UriSigner('secret'))
        );
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
                    'autoplay' => true,
                    'loop' => true,
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
