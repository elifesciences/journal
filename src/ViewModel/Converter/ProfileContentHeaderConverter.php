<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Profile;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel;

final class ProfileContentHeaderConverter implements ViewModelConverter
{
    /**
     * @param Profile $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getAffiliations()->notEmpty()) {
            $affiliations = $object->getAffiliations()
                ->map(Callback::method('getName'))
                ->map(Callback::apply('end'))
                ->toArray();

            $strapline = implode('<br>', array_unique($affiliations));
        }

        return new ViewModel\ContentHeaderSimple(
            $object->getDetails()->getPreferredName(),
            $strapline ?? null
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Profile && ViewModel\ContentHeader::class === $viewModel;
    }
}
