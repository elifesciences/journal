<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\CoverBlogArticleSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverBlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/');

        $this->converter = new CoverBlogArticleSecondaryTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $urlGenerator
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof BlogArticle) {
            yield $model;
        }
    }
}
