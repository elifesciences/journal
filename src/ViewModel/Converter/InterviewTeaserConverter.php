<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InterviewTeaserConverter implements ViewModelConverter
{
    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    /**
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('interview', ['id' => $object->getId(), 'slug' => $this->slugify->slugify($object->getInterviewee()->getPerson()->getPreferredName())]),
            $object->getImpactStatement(),
            $object->getSubTitle(),
            null,
            null,
            TeaserFooter::forNonArticle(
                Meta::withText(
                    'Interview',
                    Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
