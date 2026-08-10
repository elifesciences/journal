<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Converter\SubjectContentHeaderConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\ContentHeader;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Asset\Packages;

final class SubjectContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['subject'];
    protected $viewModelClasses = [ContentHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SubjectContentHeaderConverter(
            new ContentHeaderImageFactory(new PictureBuilderFactory(), new Packages())
        );
    }
}
