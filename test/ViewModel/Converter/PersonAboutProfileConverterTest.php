<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PersonAboutProfileConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\AboutProfile;

final class PersonAboutProfileConverterTest extends ModelConverterTestCase
{
    protected $models = ['person'];
    protected $viewModelClasses = [AboutProfile::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PersonAboutProfileConverter($this->createMock(ViewModelConverter::class), $this->createMock(PatternRenderer::class));
    }
}
