<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ArticleContentHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Authors(
                [
                    ViewModel\Author::asText('Author 1'),
                    ViewModel\Author::asText('Author 2', true),
                ]
            ));
    }
}
