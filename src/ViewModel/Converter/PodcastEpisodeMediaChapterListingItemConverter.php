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
        if ($object->getContent()->notEmpty()) {
            $meta = ViewModel\Meta::withText(implode('. ', $object->getContent()->map(function (Model $model) {
                if ($model instanceof ArticleVersion) {
                    $return = ModelName::singular($model->getType());

                    if ($model->getAuthorLine()) {
                        $return .= ' by '.$model->getAuthorLine();
                    }

                    return '<a href="'.$this->urlGenerator->generate('article', ['id' => $model->getId()]).'">'.$return.'</a>';
                } elseif ($model instanceof Collection) {
                    $return = ModelName::singular('collection').' curated by '.$model->getSelectedCurator()->getDetails()->getPreferredName();

                    if ($model->selectedCuratorEtAl()) {
                        $return .= ' et al';
                    }

                    return $return;
                }

                throw new UnexpectedValueException('Unknown type '.get_class($model));
            })->toArray()).'.');
        } else {
            $meta = null;
        }

        return new ViewModel\MediaChapterListingItem($object->getTitle(), $object->getTime(), $object->getNumber(), $object->getImpactStatement(), $meta);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapter;
    }
}
