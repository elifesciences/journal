<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\HighlightItem;
use eLife\Patterns\ViewModel\Picture;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Converts an eLife\ApiSdk\Model\Highlight object as obtained from the api-sdk (source JournalCMS) to a HighlightItem
 */
class HighlightHighlightItemConverter implements ViewModelConverter
{
    use CreatesCoverPicture;
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Highlight $object
     * @param string|null $viewModel
     * @param array $context
     * @return ViewModel
     */
    public function convert($object, string $viewModel = null, array $context = []): ViewModel
    {
        /**
         * The content linked to this highlight
         * @var Model $item
         **/
        $item = $object->getItem();

        $subjects = method_exists($item, 'getSubjects')
            ? $item->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray()
            : [];

        $authorLine = null;

        if ($item instanceof ArticleVersion) {
            $contentUrl = $this->urlGenerator->generate('article', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular($item->getType()),
                $this->urlGenerator->generate('article-type', ['type' => $item->getType()])
            );
            $authorLine = $item->getAuthorLine();
        } else if ($item instanceof BlogArticle) {
            $contentUrl = $this->urlGenerator->generate('inside-elife-article', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('blog-article'),
                $this->urlGenerator->generate('inside-elife')
            );
        } else if ($item instanceof Collection) {
            $contentUrl = $this->urlGenerator->generate('collection', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('collection'),
                $this->urlGenerator->generate('collections')
            );
        }
        else if ($item instanceof Event) {
            $contentUrl = $this->urlGenerator->generate('event', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('event'),
                $this->urlGenerator->generate('events')
            );
        } else if ($item instanceof Interview) {
            $contentUrl = $this->urlGenerator->generate('interview', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('interview'),
                $this->urlGenerator->generate('interviews')
            );
        } else if ($item instanceof PodcastEpisode) {
            $contentUrl = $this->urlGenerator->generate('podcast-episode', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('podcast-episode'),
                $this->urlGenerator->generate('podcast')
            );
        } else if ($item instanceof PodcastEpisodeChapter) {

        } else if ($item instanceof PressPackage) {
            $contentUrl = $this->urlGenerator->generate('press-pack', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('press-package'),
                $this->urlGenerator->generate('press-packs')
            );
        } else if ($item instanceof ReviewedPreprint) {
            $contentUrl = $this->urlGenerator->generate('reviewed-preprint', ['id' => $item->getId()]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('reviewed-preprint'),
                $this->urlGenerator->generate('reviewed-preprints')
            );
            $authorLine = $item->getAuthorLine();
        } else if ($item instanceof Digest) {
            $contentUrl = $this->urlGenerator->generate('digest', [$item]);
            $metaLink = new ViewModel\Link(
                ModelName::singular('digest'),
                $this->urlGenerator->generate('digests')
            );
        }
        else {
            throw new InvalidArgumentException('Unknown cover item type: '.get_class($item));
        }

        return new ViewModel\HighlightItem(
            $subjects,
            new ViewModel\Link($object->getTitle(), $contentUrl),
            ViewModel\Meta::withLink($metaLink, $this->simpleDate($item, $context)),
            $this->highlightPicture($object),
            $object->getImpactStatement(),
            $authorLine
        );
    }

    /**
     * @param Highlight $object
     * @return Picture|null
     */
    private function highlightPicture(Highlight $object): ? ViewModel\Picture
    {
        $image = $object->getThumbnail();

        // attempting to fallback on the related content's image if $image is null
        if ($image === null) {
            // TODO verify how its normalized in the api sdk if banner gets set as a thumbnail
            if ($object->getItem() instanceof HasThumbnail) {
                $image = $object->getItem()->getThumbnail();
            }
            if ($object->getItem() instanceof HasBanner) {
                $image = $object->getItem()->getBanner();
            }
        }

        if ($image === null) {
            return null;
        }

        // TODO put these numerical values somewhere else?
        return (new PictureBuilderFactory())->forImage($image, 368, 207)->build();
    }

    public function supports($object, string $viewModel = null, array $context = []): bool
    {
        return $object instanceof Highlight && $viewModel === HighlightItem::class;
    }
}