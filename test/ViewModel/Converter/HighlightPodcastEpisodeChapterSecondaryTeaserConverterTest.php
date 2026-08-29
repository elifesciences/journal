<?php

namespace test\eLife\Journal\ViewModel\Converter;

use ComposerLocator;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\Journal\ViewModel\Converter\HighlightPodcastEpisodeChapterSecondaryTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use ReflectionClass;
use Traversable;

final class HighlightPodcastEpisodeChapterSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new HighlightPodcastEpisodeChapterSecondaryTeaserConverter(
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
     * No highlight-list sample fixture currently contains a podcast-episode-chapter item, so build
     * one directly from the recommendations sample (the only fixture where this type occurs) instead
     * of going through the shared findSamples()/modelHook() fixture pipeline.
     */
    public static function samples() : Traversable
    {
        $instance = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $path = ComposerLocator::getPath('elife/api').'/dist/samples/recommendations/v1/first-page.json';
        $items = json_decode(file_get_contents($path), true)['items'];
        $chapterData = current(array_filter($items, function (array $item) {
            return 'podcast-episode-chapter' === $item['type'];
        }));

        $podcastEpisodeChapter = self::denormalizeData($chapterData, PodcastEpisodeChapterModel::class);

        $highlight = new Highlight('Highlight title', null, $podcastEpisodeChapter, 'Highlight impact statement.');

        foreach ($instance->viewModelClasses as $viewModelClass) {
            yield [$highlight, $viewModelClass];
        }
    }
}
