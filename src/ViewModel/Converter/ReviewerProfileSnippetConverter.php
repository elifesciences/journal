<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use eLife\Patterns\ViewModel;

final class ReviewerProfileSnippetConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param Reviewer $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getRole();

        if ($object->getAffiliations()) {
            $title .= '; '.implode('; ', array_map(function (Place $affiliation) {
                return $affiliation->toString();
            }, $object->getAffiliations()));
        }

        return new ViewModel\ProfileSnippet($object->getPreferredName(), $title);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Reviewer;
    }
}
