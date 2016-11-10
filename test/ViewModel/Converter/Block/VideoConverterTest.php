<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\VideoConverter;
use eLife\Patterns\ViewModel\Video;

final class VideoConverterTest extends BlockConverterTestCase
{
    protected $class = 'eLife\ApiSdk\Model\Block\Video';
    protected $viewModelClass = Video::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new VideoConverter();
    }

    public function blocks()
    {
        return [
            [
                [
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
        ];
    }
}
