<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ProfileContentHeaderProfileConverter;
use eLife\Patterns\ViewModel\ContentHeaderProfile;
use PHPUnit\Framework\Attributes\Before;

final class ProfileContentHeaderProfileConverterTest extends ModelConverterTestCase
{
    protected $models = ['profile'];
    protected $viewModelClasses = [ContentHeaderProfile::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ProfileContentHeaderProfileConverter($this->stubUrlGenerator());
    }
}
