<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ListHeading;
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
        array $vorCitations = null,
        ArticleVersion $item
    )
    {
        $totalStatistics = [];
        $barCharts = [];
        $numberOfTotalCitations = $totalCitations->getHighest()->getCitations();
        $numberOfCitationsForVersions = self::calculateCitationsForVersions($vorCitations);
        $numberOfCitationsForUmbrellaDoi = $numberOfTotalCitations - $numberOfCitationsForVersions;

        if ($totalPageViews) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('views', $totalPageViews);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
        }

        if ($totalDownloads) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('downloads', $totalDownloads);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
        }

        if ($totalCitations) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('citations', $numberOfTotalCitations);
        }

        $totalStatisticsDescription = new Paragraph('Views, downloads and citations are aggregated across all versions of this paper published by eLife.');

        $metricParts = [];
        $metricParts[] = new ViewModel\StatisticCollection(...$totalStatistics);
        $metricParts[] = $totalStatisticsDescription;

        if ($request->query->get('showVorMetrics') === 'true') {
            $metricParts[] = new ListHeading('Citations by DOI');
            // $metricParts[] = new Paragraph('Umbrella DOI: https://doi.org/'.$item->getDoi());
            $metricParts[] = new Paragraph('Umbrella DOI: '.self::constructDoiLink($item->getDoi()));
            $metricParts[] = new Paragraph($numberOfCitationsForUmbrellaDoi.' citations');
            if ($vorCitations) {
                foreach ($vorCitations as $i => $citations) {
                    if ($citations) {
                        $versionNumber = $i + 1;
                        $highestCountOfCitationsForAVersion = $citations->getHighest()->getCitations();
                        if ($highestCountOfCitationsForAVersion > 0) {
                            $vorStatistics = ViewModel\Statistic::fromNumber('Citations for version '.$versionNumber, $highestCountOfCitationsForAVersion);
                            $metricParts[] = new ViewModel\StatisticCollection($vorStatistics);
                        }
                    }
                }
            }
        }

        return array_merge($metricParts, $barCharts);
    }
    private static function calculateCitationsForVersions(array $vorCitations)
    {
        $sumOfAllVersionSpecificCitations = 0;
        if ($vorCitations) {
            foreach ($vorCitations as $i => $citations) {
                if ($citations) {
                    $sumOfAllVersionSpecificCitations += $citations->getHighest()->getCitations();
                }
            }
        }
        return $sumOfAllVersionSpecificCitations;
    }

    private static function constructDoiLink(string $doi)
    {
        $url = 'https://doi.org/'.$doi;
        return '<a href="'.$url.'">'.$url.'</a>';
    }
};
