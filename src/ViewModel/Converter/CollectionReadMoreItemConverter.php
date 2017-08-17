<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionReadMoreItemConverter implements ViewModelConverter
{
    use CreatesDate;

    private $patternRenderer;
    private $urlGenerator;

    public function __construct(PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator)
    {
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Collection $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $curatedBy = 'Curated by '.$object->getSelectedCurator()->getDetails()->getPreferredName();
        if ($object->selectedCuratorEtAl()) {
            $curatedBy .= ' et al.';
        }

        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                $object->getTitle(),
                $this->urlGenerator->generate('collection', [$object]),
                $object->getSubjects()->map(function (Subject $subject) {
                    return new ViewModel\Link($subject->getName());
                })->toArray(),
                $curatedBy,
                ViewModel\Meta::withLink(
                    new ViewModel\Link(ModelName::singular('collection'), $this->urlGenerator->generate('collections')),
                    $this->simpleDate($object, $context)
                )
            ),
            $object->getImpactStatement() ? $this->patternRenderer->render(new Paragraph($object->getImpactStatement())) : null,
            $context['isRelated'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\ReadMoreItem::class === $viewModel;
    }
}
