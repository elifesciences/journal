<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\Paginator;
use eLife\Journal\ViewModel\EmptyListing;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ListHeading;

final class PaginatorListingReadMoreConverter implements ViewModelConverter
{
    /**
     * @param Paginator $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $type = $context['type'] ?? null;

        $heading = new ListHeading($context['heading'] ?? 'Further reading');
        $loadMoreText = $type ? 'More '.$type : 'Load more';
        $prevText = trim('Newer '.$type);
        $nextText = trim('Older '.$type);
        $emptyText = $context['emptyText'] ?? (trim('No '.($type ?? 'items').' available.'));

        if (0 === count($object->getItems())) {
            return new EmptyListing($heading, $emptyText);
        } elseif ($object->getCurrentPage() > 1) {
            return ViewModel\ListingReadMore::withPagination(
                $object->getItems(),
                ViewModel\Pager::subsequentPage(
                    new ViewModel\Link($prevText, $object->getPreviousPagePath()),
                    $object->getNextPage()
                        ? new ViewModel\Link($nextText, $object->getNextPagePath())
                        : null
                )
            );
        } elseif ($object->getNextPage()) {
            return ViewModel\ListingReadMore::withPagination(
                $object->getItems(),
                $object->getNextPage()
                    ? ViewModel\Pager::firstPage(new ViewModel\Link($loadMoreText, $object->getNextPagePath()), 'listing')
                    : null,
                $heading,
                'listing'
            );
        }

        return ViewModel\ListingReadMore::basic($object->getItems(), $heading, 'listing');
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Paginator && ViewModel\ListingReadMore::class === $viewModel;
    }
}
