<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReviewedPreprintReadMoreItemConverter implements ViewModelConverter
{
    use CanConvertContent;
    use CreatesDate;

    private $viewModelConverter;
    private $patternRenderer;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ReviewedPreprint $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                $object->getTitle(),
                $this->urlGenerator->generate('reviewed-preprint', [$object]),
                $object->getSubjects()->map(function (Subject $subject) {
                    return new ViewModel\Link($subject->getName());
                })->toArray(),
                $object->getAuthorLine(),
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('reviewed-preprint'),
                        $this->urlGenerator->generate('reviewed-preprints')
                    ),
                    $this->simpleDate($object, $context)
                )
            ),
            '',
            $context['isRelated'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReviewedPreprint && ViewModel\ReadMoreItem::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
