<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightArticleSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class HighlightArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightArticleSecondaryTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof ArticleVersion) {
            yield $model;
        }
    }
}
