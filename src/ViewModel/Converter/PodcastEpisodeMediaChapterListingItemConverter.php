<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

final class PodcastEpisodeMediaChapterListingItemConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PodcastEpisodeChapter $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $contentSources = $object->getContent()->map(function (Model $model) {
            if ($model instanceof ArticleVersion) {
                $name = ModelName::singular($model->getType());
                $url = $this->urlGenerator->generate('article', [$model]);
                if ($model->getAuthorLine()) {
                    $text = ' by '.$model->getAuthorLine();
                }

                return new ViewModel\ContentSource(new ViewModel\Link($name, $url), $text ?? null);
            } elseif ($model instanceof Collection) {
                $name = ModelName::singular('collection');
                $url = $this->urlGenerator->generate('collection', [$model]);
                $text = ' edited by '.$model->getSelectedCurator()->getDetails()->getPreferredName();

                if ($model->selectedCuratorEtAl()) {
                    $text .= ' et al.';
                }

                return new ViewModel\ContentSource(new ViewModel\Link($name, $url), $text);
            }

            throw new UnexpectedValueException('Unknown type '.get_class($model));
        })->toArray();

        return new ViewModel\MediaChapterListingItem($object->getTitle(), $object->getTime(), $object->getNumber(), $object->getImpactStatement(), $contentSources);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapter;
    }
}
