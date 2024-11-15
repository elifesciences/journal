<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ReviewedPreprintTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ReviewedPreprintTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['reviewed-preprint'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ReviewedPreprintTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            )));
    }

    /**
     * @test
     * @dataProvider samples
     */
    final public function it_shows_the_reviewed_preprint_status($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->assertArrayHasKey('articleStatus', $array['footer']['meta']);
        $this->assertContains($array['footer']['meta']['articleStatus'], [ViewModel\Meta::STATUS_NOT_REVISED, ViewModel\Meta::STATUS_REVISED]);
        $this->assertArrayHasKey('articleStatusColorClass', $array['footer']['meta']);
    }

    /**
     * @test
     * @dataProvider samples
     */
    final public function it_shows_the_reviewed_preprint_version_in_the_meta_version($model, string $viewModelClass)
    {
        $viewModel = $this->converter->convert($model, $viewModelClass, $this->context);

        $array = $viewModel->toArray();
        $this->markTestSkipped();
        $this->assertArrayHasKey('version', $array['footer']['meta']);
    }
}
