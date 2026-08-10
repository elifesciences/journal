<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ReviewedPreprintTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class ReviewedPreprintTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['reviewed-preprint'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ReviewedPreprintTeaserConverter(
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

    #[Test]
    #[DataProvider('samples')]
    final public function it_shows_the_reviewed_preprint_status($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->assertArrayHasKey('articleStatus', $array['footer']['meta']);
        $this->assertContains($array['footer']['meta']['articleStatus'], [ViewModel\Meta::STATUS_NOT_REVISED, ViewModel\Meta::STATUS_REVISED]);
        $this->assertArrayHasKey('articleStatusColorClass', $array['footer']['meta']);
        $this->assertContains($array['footer']['meta']['articleStatusColorClass'], [ViewModel\Meta::COLOR_NOT_REVISED, ViewModel\Meta::COLOR_REVISED]);
    }

    #[Test]
    #[DataProvider('samples')]
    final public function it_shows_the_reviewed_preprint_date($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->markTestIncomplete('The date is not mandatory in the schema so we cannot assert consistently on samples.');
        $this->assertArrayHasKey('date', $array['footer']['meta']);
    }

    #[Test]
    #[DataProvider('samples')]
    final public function it_shows_the_reviewed_preprint_version_in_the_meta_version($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->assertArrayHasKey('version', $array['footer']['meta']);
    }

    #[Test]
    #[DataProvider('samples')]
    final public function it_does_not_show_any_url_or_text($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->assertFalse($array['footer']['meta']['url']);
        $this->assertArrayNotHasKey('text', $array['footer']['meta']);
    }
}
