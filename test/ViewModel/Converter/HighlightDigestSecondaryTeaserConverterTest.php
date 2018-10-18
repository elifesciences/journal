<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightDigestSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use Traversable;

final class HighlightDigestSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightDigestSecondaryTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            )))
        ;
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Digest) {
            yield $model;
        }
    }
}
