<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Converter\SubjectBlockLinkConverter;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class SubjectBlockLinkConverterTest extends ModelConverterTestCase
{
    protected $models = ['subject'];
    protected $viewModelClasses = [ViewModel\BlockLink::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SubjectBlockLinkConverter(
            $this->stubUrlGenerator(),
            new PictureBuilderFactory()
        );
    }
}
