<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\Paragraph;

class Metrics
{

    public static function build(
        string $apiEndPoint,
        string $itemId,
        ArticleVersion $item,
        int $totalPageViews = null,
        int $totalDownloads = null,
        CitationsMetric $totalCitations = null,
        array $vorCitations = null
    )
    {
        $totalStatistics = [];
        $barCharts = [];

        if ($totalCitations) {
            $numberOfTotalCitations = $totalCitations->getHighest()->getCitations();
            $numberOfCitationsForVersions = self::calculateCitationsForVersions($vorCitations);
            $numberOfCitationsForUmbrellaDoi = $numberOfTotalCitations - $numberOfCitationsForVersions;
            if ($numberOfCitationsForUmbrellaDoi > 0) {
                $metricPartsVors[] = self::constructUmbrellaDoiStatisticCollection($numberOfCitationsForUmbrellaDoi, $item->getDoi());
            }
        }

        if ($totalPageViews) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('views', $totalPageViews);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'page-views', $apiEndPoint, 'page-views', 'month');
        }

        if ($totalDownloads) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber('downloads', $totalDownloads);
            $barCharts[] = new ViewModel\BarChart($itemId, 'article', 'downloads', $apiEndPoint, 'downloads', 'month');
        }

        if ($totalCitations) {
            $totalStatistics[] = ViewModel\Statistic::fromNumber(self::pluralise('citation', $numberOfTotalCitations !== 1), $numberOfTotalCitations);
        }

        $totalStatisticsDescription = new Paragraph('Views, downloads and citations are aggregated across all versions of this paper published by eLife.');

        $metricParts = [];
        $metricPartsVors = [];
        if (sizeof($totalStatistics) > 0) {
            $metricParts[] = new ViewModel\StatisticCollection(...$totalStatistics);
            $metricParts[] = $totalStatisticsDescription;
        }

        if ($vorCitations) {
            foreach ($vorCitations as $i => $citations) {
                if ($citations && $citations->getHighest()->getCitations() > 0) {
                    $versionNumber = $i + 1;
                    $metricPartsVors[] = self::constructVorStatisticCollection($vorCitations, $citations, $versionNumber);
                }
            }
        }
        if (sizeof($metricPartsVors) > 0) {
            array_unshift($metricPartsVors, new ListHeading('Citations by DOI'));
        }

        $altmetric = [new ViewModel\Altmetric($item->getDoi(), 'medium-donut', true)];


        return array_merge($metricParts, $metricPartsVors, $altmetric, $barCharts);
    }

    private static function constructUmbrellaDoiStatisticCollection(int $numberOfCitationsForUmbrellaDoi, string $doi)
    {
        return new ViewModel\StatisticCollection(ViewModel\Statistic::fromNumber(
            self::pluralise('citation', $numberOfCitationsForUmbrellaDoi !== 1).' for umbrella DOI '.self::constructDoiLink($doi),
            $numberOfCitationsForUmbrellaDoi,
            'true'
        ));
    }

    private static function constructVorStatisticCollection(array $vorCitations, CitationsMetric $citations, int $versionNumber)
    {
        $highestCountOfCitationsForAVersion = $citations->getHighest()->getCitations();
        $isLatestVersion = $versionNumber === sizeof($vorCitations);
        $versionUri = $citations->getHighest()->getUri();
        return new ViewModel\StatisticCollection(ViewModel\Statistic::fromNumber(
            self::constructLabel($versionNumber, $versionUri, $isLatestVersion, $highestCountOfCitationsForAVersion > 1),
            $highestCountOfCitationsForAVersion,
            'true'
        ));
    }

    private static function calculateCitationsForVersions(array $vorCitations): int
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

    private static function constructDoiLink(string $doi): string
    {
        $url = 'https://doi.org/'.$doi;
        return '<a href="'.$url.'">'.$url.'</a>';
    }

    private static function constructDoiLinkFromUri(string $uri): string
    {
        return '<a href="'.$uri.'">'.$uri.'</a>';
    }

    private static function constructLabel(int $versionNumber, string $versionUri, bool $isLatestVersion = false, bool $isPlural = false): string
    {
        $versionLabel = $isLatestVersion ? 'Version of Record ' : 'Reviewed Preprint v'.$versionNumber;
        return self::pluralise('citation', $isPlural).' for '.$versionLabel.' '.self::constructDoiLinkFromUri($versionUri);
    }

    private static function pluralise(string $word, bool $pluralise): string
    {
        return $pluralise ? $word.'s' : $word;
    }
};
