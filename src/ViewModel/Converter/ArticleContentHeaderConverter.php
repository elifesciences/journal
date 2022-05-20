<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class ArticleContentHeaderConverter implements ViewModelConverter
{
    use CanConvertContent;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $isMagazine = $context['isMagazine'] ?? false;

        $breadcrumb = ($isMagazine || 'feature' === $object->getType()) ? [
            new ViewModel\Link(
                'Magazine',
                $this->urlGenerator->generate('magazine')
            ),
        ] : [];

        $breadcrumb[] = new ViewModel\Link(
            ModelName::singular($object->getType()),
            $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
        );

        $subjects = $object->getSubjects()->map(function (Subject $subject) {
            return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
        })->toArray();

        $authors = (!$isMagazine && $object->getAuthors()->notEmpty()) ? $this->convertTo($object, ViewModel\Authors::class) : null;

        // @todo - consider just using impactStatement for magazine articles, rather than abstract
        $impactStatement = ($isMagazine && $object->getAbstract()) ? implode(' ', $object->getAbstract()->getContent()->map(function (Paragraph $item) {
            return $item->getText();
        })->toArray()) : null;

        if ($date = $this->simpleDate($object, ['date' => 'published'] + $context)) {
            $meta = ViewModel\MetaNew::withDate($date);
        } else {
            $meta = null;
        }

        return new ViewModel\ContentHeaderNew(
            $object->getFullTitle(),
            null,
            $impactStatement,
            true,
            new ViewModel\Breadcrumb($breadcrumb),
            $subjects,
            null,
            $authors,
            '#downloads',
            '#cite-this-article',
            new ViewModel\SocialMediaSharersNew(
                strip_tags($object->getFullTitle()),
                "https://doi.org/{$object->getDoi()}"
            ),
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            null,
            $meta,
            $object->getDoi() ? new ViewModel\Doi($object->getDoi()) : null,
            LicenceUri::forCode($object->getCopyright()->getLicense())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\ContentHeaderNew::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
