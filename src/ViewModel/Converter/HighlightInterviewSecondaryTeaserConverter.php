<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HighlightInterviewSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    /**
     * @param Highlight $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Interview $interview */
        $interview = $object->getItem();

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('interview', ['id' => $interview->getId(), 'slug' => $this->slugify->slugify($interview->getInterviewee()->getPerson()->getPreferredName())]),
            null,
            $this->createContextLabel($interview),
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            ViewModel\TeaserFooter::forNonArticle(
                Meta::withText('Interview', $this->simpleDate($interview, $context))
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Highlight && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof Interview;
    }
}
