<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\QuestionConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class QuestionConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Question::class;
    protected $viewModelClasses = [ViewModel\ArticleSection::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new QuestionConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
