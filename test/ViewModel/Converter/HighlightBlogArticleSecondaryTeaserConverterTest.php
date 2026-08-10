<?php

namespace test\eLife\Journal\ViewModel\Converter;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Highlight;
use eLife\Journal\ViewModel\Converter\HighlightBlogArticleSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;
use function GuzzleHttp\Promise\promise_for;

final class HighlightBlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightBlogArticleSecondaryTeaserConverter(
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
     * No highlight-list sample fixture currently contains a blog-article item, so build one directly
     * instead of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $highlight = new Highlight(
            'Highlight title',
            null,
            new BlogArticle(
                'blog-article-id',
                'Blog article title',
                new DateTimeImmutable('2008-10-01 01:23:45'),
                null,
                'Blog article impact statement',
                promise_for(null),
                new EmptySequence(),
                new EmptySequence()
            ),
            'Highlight impact statement.'
        );

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
