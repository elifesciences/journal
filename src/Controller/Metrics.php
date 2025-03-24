<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\CitationsMetric;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Paragraph;

class Metrics
{

    public static function build(
        string $apiEndPoint,
        string $itemId,
        int $pageViews = null,
        int $downloads = null,
        CitationsMetric $citations = null
    )
    {
        $statistics = [];
        $statisticsExtra = [];
        $statisticsDescription = [];

        if ($pageViews) {
            $statistics[] = ViewModel\Statistic::fromNumber('views', $pageViews);
            $statisticsExtra[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
        }

        if ($downloads) {
            $statistics[] = ViewModel\Statistic::fromNumber('downloads', $downloads);
            $statisticsExtra[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
        }

        if ($citations) {
            $statistics[] = ViewModel\Statistic::fromNumber('citations', $citations->getHighest()->getCitations());
        }

        $statisticsDescription[] = new Paragraph('Views, downloads and citations are aggregated across all versions of this paper published by eLife.');

        return array_merge([new ViewModel\StatisticCollection(...$statistics)], $statisticsDescription, $statisticsExtra);
    }
};
