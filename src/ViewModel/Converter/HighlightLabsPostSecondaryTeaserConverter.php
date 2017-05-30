<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\LabsPost;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HighlightLabsPostSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Highlight $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var LabsPost $post */
        $post = $object->getItem();

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('labs-post', ['id' => $post->getId()]),
            null,
            $this->createContextLabel($post),
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withText(
                    'Post: '.str_pad($post->getId(), 3, '0', STR_PAD_LEFT),
                    $this->simpleDate($post, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Highlight && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof LabsPost;
    }
}
