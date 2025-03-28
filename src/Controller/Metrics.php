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
        array $vorCitations = null
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
            if ($vorCitations) {
                $vorCitationsNumber = $vorCitations['3'];
                $vorStatistics[] = ViewModel\Statistic::fromNumber('citations', $vorCitationsNumber->getHighest()->getCitations());
                $metricParts[] = new ViewModel\StatisticCollection(...$vorStatistics);
                $metricParts[] = new Paragraph('Citations for the Version of Record.');
            }
        }

        return array_merge($metricParts, $barCharts);
    }
};
