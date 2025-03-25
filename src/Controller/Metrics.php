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
        CitationsMetric $totalCitations = null,
        int $vorPageViews = null,
        int $vorDownloads = null,
        CitationsMetric $vorCitations = null
    )
    {
        $totalStatistics = [];
        $barCharts = [];

        if ($totalPageViews) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('views', $totalPageViews);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
        }

        if ($totalDownloads) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('downloads', $totalDownloads);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
        }

        if ($totalCitations) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('citations', $totalCitations->getHighest()->getCitations());
        }

        $totalStatisticsDescription = new Paragraph('Views, downloads and citations are aggregated across all versions of this paper published by eLife.');

        $metricParts = [];
        $metricParts[] = new ViewModel\StatisticCollection(...$totalStatistics);
        $metricParts[] = $totalStatisticsDescription;

        $vorStatistics = [];

        if($vorCitations !==null) {
            if ($vorCitations->getHighest()->getCitations() > 0) {
                $vorStatistics[] = ViewModel\Statistic::fromNumber('citations', $vorCitations->getHighest()->getCitations());
            }
            $metricParts[] = new ViewModel\StatisticCollection(...$vorStatistics);
            $metricParts[] = new Paragraph('Citations for the Version of Record.');
        }

        return array_merge($metricParts, $barCharts);
    }
};
