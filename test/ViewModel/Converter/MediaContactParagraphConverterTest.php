<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\ViewModel\Converter\MediaContactParagraphConverter;
use eLife\Patterns\ViewModel\Paragraph;
use Traversable;

final class MediaContactParagraphConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [Paragraph::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new MediaContactParagraphConverter();
    }

    /**
     * @param PressPackage $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getMediaContacts();
    }
}
