<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\QuestionConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class QuestionConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Question::class;
    protected $viewModelClasses = [ViewModel\ArticleSection::class];

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
}
