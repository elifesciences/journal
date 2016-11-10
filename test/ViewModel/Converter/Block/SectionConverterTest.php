<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\SectionConverter;
use eLife\ApiSdk\Model\Block\Section;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;

final class SectionConverterTest extends BlockConverterTestCase
{
    protected $class = Section::class;
    protected $viewModelClass = ArticleSection::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SectionConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    public function blocks()
    {
        return [
            [
                [
                    'title' => 'Results',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Lorem ipsum...',
                        ]
                    ],
                ],
            ],
        ];
    }
}
