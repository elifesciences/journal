<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InterviewContentHeaderConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['interview'];
    protected $class = Interview::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new InterviewContentHeaderConverter($this->urlGenerator);
    }
}
