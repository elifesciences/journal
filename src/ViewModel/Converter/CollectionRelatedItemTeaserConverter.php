<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\ModelRelationship;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionRelatedItemTeaserConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesTeaserImage;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Collection $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $curatedBy = 'Edited by '.$object->getSelectedCurator()->getDetails()->getPreferredName();
        if ($object->selectedCuratorEtAl()) {
            $curatedBy .= ' et al.';
        }

        return ViewModel\Teaser::relatedItem(
            $object->getTitle(),
            $this->urlGenerator->generate('collection', [$object]),
            $curatedBy,
            new ViewModel\ContextLabel(new ViewModel\Link(ModelRelationship::get($context['from'], 'collection', $context['related'] ?? false), 'saeed')),
            $this->bigTeaserImage($object)
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && !empty($context['from']) && ViewModel\Teaser::class === $viewModel && 'relatedItem' === ($context['variant'] ?? null);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
