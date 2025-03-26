<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\CitationsMetric;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;

class Metrics
{

    public static function build(
        Request $request,
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

        if ($request->query->get('showVorMetrics') === 'true') {
            $vorStatistics = [];
            $barCharts = [];
            if ($vorPageViews) {
                $vorStatistics[] = ViewModel\Statistic::fromNumber('views', $vorPageViews);
                $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
            }

            if ($vorDownloads) {
                $vorStatistics[] = ViewModel\Statistic::fromNumber('downloads', $vorDownloads);
                $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
            }

            if ($vorCitations) {
                $vorStatistics[] = ViewModel\Statistic::fromNumber('citations', $vorCitations->getHighest()->getCitations());
                $metricParts[] = new ViewModel\StatisticCollection(...$vorStatistics);
                $metricParts[] = new Paragraph('Views, downloads and citations for the Version of Record. (Charts show only views and downloads for the version of record).');
            }
        }

        return array_merge($metricParts, $barCharts);
    }
};
