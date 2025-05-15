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
            $umbrellaDoiStatistic = ViewModel\Statistic::fromNumber(
                self::pluralise('citation', true).' for umbrella DOI '.self::constructDoiLink($item->getDoi()),
                $numberOfCitationsForUmbrellaDoi,
                'true'
            );
            $metricParts[] = new ViewModel\StatisticCollection($umbrellaDoiStatistic);
            if ($vorCitations) {
                foreach ($vorCitations as $i => $citations) {
                    if ($citations) {
                        $versionNumber = $i + 1;
                        $isLatestVersion = $versionNumber === sizeof($vorCitations);
                        $highestCountOfCitationsForAVersion = $citations->getHighest()->getCitations();
                        $versionUri = $citations->getHighest()->getUri();
                        if ($highestCountOfCitationsForAVersion > 0) {
                            $vorStatistics = ViewModel\Statistic::fromNumber(
                                self::constructLabel($versionNumber, $versionUri, $isLatestVersion, $highestCountOfCitationsForAVersion === 1),
                                $highestCountOfCitationsForAVersion,
                                'true'
                            );
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

    private static function constructDoiLinkFromUri(string $uri)
    {
        return '<a href="'.$uri.'">'.$uri.'</a>';
    }

    private static function constructLabel(int $versionNumber, string $versionUri, bool $isLatestVersion = false, bool $isSingular = false): string
    {
        $versionLabel = $isLatestVersion ? 'Version of Record ' : 'Reviewed Preprint V'.$versionNumber;
        $citationLabel = $isSingular ? 'citation' : 'citations';
        return $citationLabel.' for '.$versionLabel.' '.self::constructDoiLinkFromUri($versionUri);
    }

    private static function pluralise(string $word, bool $pluralise)
    {
        return $pluralise ? $word.'s' : $word;
    }
};
