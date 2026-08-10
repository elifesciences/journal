<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ArticleDecisionLetterHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\DecisionLetterHeader;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ProfileSnippet;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class ArticleDecisionLetterHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $viewModelClasses = [DecisionLetterHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ArticleDecisionLetterHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturnCallback(function ($input) {
                if ($input instanceof Block) {
                    return new Paragraph('...');
                }

                return new ProfileSnippet('name', 'title');
            });

        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }

    /**
     * @param ArticleVoR $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getDecisionLetter()) {
            yield $model;
        }
    }
}
