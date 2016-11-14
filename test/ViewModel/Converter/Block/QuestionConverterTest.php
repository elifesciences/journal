<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Question;
use eLife\Journal\ViewModel\Converter\Block\QuestionConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\ArticleSection;

final class QuestionConverterTest extends BlockConverterTestCase
{
    protected $class = Question::class;
    protected $viewModelClass = ArticleSection::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new QuestionConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'question' => 'What is the answer?',
                    'answer' => [
                        [
                            'type' => 'paragraph',
                            'text' => '42',
                        ],
                    ],
                ],
            ],
        ];
    }
}
