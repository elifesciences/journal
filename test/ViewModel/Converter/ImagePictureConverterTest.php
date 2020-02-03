<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ImagePictureConverter;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Traversable;

final class ImagePictureConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor', 'collection', 'digest', 'interview', 'labs-post', 'podcast-episode', 'subject'];
    protected $viewModelClasses = [ViewModel\Picture::class];
    protected $context = ['width' => 100];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImagePictureConverter(new PictureBuilderFactory());
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model instanceof HasBanner && $model->getBanner()) {
            yield $model->getBanner();
        }

        if ($model instanceof HasThumbnail && $model->getThumbnail()) {
            yield $model->getThumbnail();
        }
    }
}
