<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InterviewSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['interview'];
    protected $class = Interview::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new InterviewSecondaryTeaserConverter($this->urlGenerator);
    }
}
