<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\Journal\ViewModel\Converter\CoverBlogArticleHeroBannerConverter;
use eLife\Journal\ViewModel\Converter\CoverPodcastEpisodeHeroBannerConverter;
use eLife\Patterns\ViewModel\HeroBanner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

final class CoverPodcastEpisodeHeroBannerConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [HeroBanner::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $this->converter = new CoverPodcastEpisodeHeroBannerConverter(
            $urlGenerator
        );
    }

    /**
     * @param Cover $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof PodcastEpisode) {
            yield $model;
        }
    }
}
