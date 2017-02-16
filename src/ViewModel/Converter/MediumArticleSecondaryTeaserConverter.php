<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\MediumArticle;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;

final class MediumArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesTeaserImage;

    /**
     * @param MediumArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getThumbnail()) {
            $image = $this->smallTeaserImage($object);
        } else {
            $image = null;
        }

        return Teaser::secondary(
            $object->getTitle(),
            $object->getUri(),
            null,
            null,
            $image,
            TeaserFooter::forNonArticle(Meta::withDate($this->simpleDate($object, $context)))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof MediumArticle && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
