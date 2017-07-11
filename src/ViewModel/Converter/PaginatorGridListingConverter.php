<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\Paginator;
use eLife\Journal\ViewModel\EmptyListing;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ListHeading;

final class PaginatorGridListingConverter implements ViewModelConverter
{
    /**
     * @param Paginator $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $type = $context['type'] ?? null;

        $heading = new ListHeading($context['heading'] ?? trim('Latest '.$type));
        $loadMoreText = $type ? 'More '.$type : 'Load more';
        $prevText = trim('Newer '.$type);
        $nextText = trim('Older '.$type);
        $emptyText = trim('No '.($type ?? 'items').' available.');

        if (0 === count($object->getItems())) {
            return new EmptyListing($heading, $emptyText);
        } elseif ($object->getCurrentPage() > 1) {
            return ViewModel\GridListing::forTeasers(
                $object->getItems(),
                null,
                ViewModel\Pager::subsequentPage(
                    new ViewModel\Link($prevText, $object->getPreviousPagePath()),
                    $object->getNextPage()
                        ? new ViewModel\Link($nextText, $object->getNextPagePath())
                        : null
                )
            );
        } elseif ($object->getNextPage()) {
            return ViewModel\GridListing::forTeasers(
                $object->getItems(),
                $heading,
                $object->getNextPage()
                    ? ViewModel\Pager::firstPage(new ViewModel\Link($loadMoreText, $object->getNextPagePath()), 'listing')
                    : null,
                'listing'
            );
        }

        return ViewModel\GridListing::forTeasers($object->getItems(), $heading);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Paginator && ViewModel\GridListing::class === $viewModel;
    }
}
