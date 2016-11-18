<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Link;

final class LinksToSections
{
    public static function of($body) : array
    {
        return array_filter(array_map(
            function (ViewModel $viewModel) {
                if ($viewModel instanceof ArticleSection) {
                    return new Link($viewModel['title'], '#'.$viewModel['id']);
                }

                return null;
            },
            count($body) > 1 ? $body : []
        ));
    }

}
