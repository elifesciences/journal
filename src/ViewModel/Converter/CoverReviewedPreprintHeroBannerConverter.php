<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverReviewedPreprintHeroBannerConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesCoverPicture;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var ReviewedPreprint $reviewedPreprint */
        $reviewedPreprint = $object->getItem();

        return new ViewModel\HeroBanner(
            $reviewedPreprint->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('reviewed-preprint', ['id' => $reviewedPreprint->getId()])),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('reviewed-preprint'),
                    $this->urlGenerator->generate('reviewed-preprints')
                ),
                $this->simpleDate($reviewedPreprint, $context)
            ),
            $this->heroBannerCoverPicture($object),
            $object->getImpactStatement(),
            $reviewedPreprint->getAuthorLine()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HeroBanner::class === $viewModel && $object->getItem() instanceof ReviewedPreprint;
    }
}
