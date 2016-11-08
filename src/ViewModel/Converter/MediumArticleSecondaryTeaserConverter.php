<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\MediumArticle;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;

final class MediumArticleSecondaryTeaserConverter implements ViewModelConverter
{
    /**
     * @param MediumArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getImage()) {
            $image = TeaserImage::small(
                $object->getImage()->getSize('1:1')->getImage(70),
                $object->getImage()->getAltText(),
                [
                    140 => $object->getImage()->getSize('1:1')->getImage(140),
                    70 => $object->getImage()->getSize('1:1')->getImage(70),
                ]
            );
        } else {
            $image = null;
        }

        return Teaser::secondary(
            $object->getTitle(),
            $object->getUri(),
            null,
            null,
            $image,
            TeaserFooter::forNonArticle(Meta::withDate(new Date($object->getPublishedDate())))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof MediumArticle && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
