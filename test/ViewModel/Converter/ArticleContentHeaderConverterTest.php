<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ContentHeaderNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleContentHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new ViewModel\Authors(
                [
                    ViewModel\Author::asText('Author 1'),
                    ViewModel\Author::asText('Author 2', true),
                ]
            )));
    }
}
