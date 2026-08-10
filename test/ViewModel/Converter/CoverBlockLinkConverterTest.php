<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\Journal\ViewModel\Converter\CoverBlockLinkConverter;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class CoverBlockLinkConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [ViewModel\BlockLink::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->context['link'] = new ViewModel\Link('link');

        $this->converter = new CoverBlockLinkConverter(new PictureBuilderFactory());
    }
}
