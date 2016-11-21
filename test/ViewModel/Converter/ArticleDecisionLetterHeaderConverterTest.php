<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ArticleDecisionLetterHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\DecisionLetterHeader;
use eLife\Patterns\ViewModel\ProfileSnippet;

final class ArticleDecisionLetterHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = DecisionLetterHeader::class;
    protected $selectSamples = ['complete.json'];

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
}
