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
        int $totalPageViews = null,
        int $totalDownloads = null,
        CitationsMetric $totalCitations = null
    )
    {
        $totalStatistics = [];
        $barChartsForTotals = [];

        if ($totalPageViews) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('views', $totalPageViews);
            $barChartsForTotals[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
        }

        if ($totalDownloads) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('downloads', $totalDownloads);
            $barChartsForTotals[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
        }

        if ($totalCitations) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('citations', $totalCitations->getHighest()->getCitations());
        }

        $totalStatisticsDescription = new Paragraph('Views, downloads and citations are aggregated across all versions of this paper published by eLife.');

        return array_merge([new ViewModel\StatisticCollection(...$totalStatistics)], [$totalStatisticsDescription], $barChartsForTotals);
    }
};
