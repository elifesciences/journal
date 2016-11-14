<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\YouTubeConverter;
use eLife\Patterns\ViewModel\IFrame;

final class YouTubeConverterTest extends BlockConverterTestCase
{
    protected $class = 'eLife\ApiSdk\Model\Block\YouTube';
    protected $viewModelClass = IFrame::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new YouTubeConverter();
    }

    public function blocks()
    {
        return [
            'minimum' => [
                [
                    'id' => 'dQw4w9WgXcQ',
                    'width' => 800,
                    'height' => 600,
                ],
            ],
        ];
    }

    protected function unsupportedBlockData()
    {
        return [
            'type' => 'image',
            'alt' => 'Image 1',
            'uri' => 'https://example.com/image1',
        ];
    }
}
