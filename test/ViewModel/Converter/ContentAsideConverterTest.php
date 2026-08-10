<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ContentAsideConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ContentAsideConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ContentAside::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ContentAsideConverter(
            $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );
    }
}
