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
use Traversable;

final class ArticleDecisionLetterHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $viewModelClasses = [DecisionLetterHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleDecisionLetterHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(function ($input) {
                if ($input instanceof Block) {
                    return new Paragraph('...');
                }

                return new ProfileSnippet('name', 'title');
            }));

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
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
