<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SubjectContentHeaderConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\ContentHeader;
use Symfony\Component\Asset\Packages;

final class SubjectContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['subject'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SubjectContentHeaderConverter(
            new ContentHeaderImageFactory(new PictureBuilderFactory(), new Packages())
        );
    }
}
