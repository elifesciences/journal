<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class AnnualReportContext extends Context
{
    private $numberOfAnnualReports;

    /**
     * @Given /^there are (\d+) annual reports$/
     */
    public function thereAreAnnualReports(int $number)
    {
        $this->numberOfAnnualReports = $number;

        $annualReports = [];

        for ($year = 2011 + $number; $year >= 2012; --$year) {
            $annualReports[] = [
                'year' => $year,
                'uri' => 'http://www.example.com/',
                'title' => "$year Annual Report",
                'image' => [
                    'uri' => 'https://www.example.com/iiif/image',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://www.example.com/image.jpg',
                        'filename' => 'image.jpg',
                    ],
                    'size' => [
                        'width' => 800,
                        'height' => 600,
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/annual-reports?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.annual-report-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.annual-report-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$annualReports[0]],
                ])
            )
        );

        foreach (array_chunk($annualReports, 6) as $year => $annualReportsChunk) {
            $page = $year + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/annual-reports?page=$page&per-page=6&order=desc",
                    ['Accept' => 'application/vnd.elife.annual-report-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.annual-report-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $annualReportsChunk,
                    ])
                )
            );

            foreach ($annualReportsChunk as $annualReport) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        "http://api.elifesciences.org/annual-reports/{$annualReport['year']}",
                        ['Accept' => 'application/vnd.elife.annual-report+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.annual-report+json; version=1'],
                        json_encode($annualReport)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go the annual reports page$/
     */
    public function iGoTheAnnualReportsPage()
    {
        $this->visitPath('/annual-reports');
    }

    /**
     * @When /^I load more annual reports/
     */
    public function iLoadMoreAnnualReports()
    {
        $this->getSession()->getPage()->clickLink('More annual reports');
    }

    /**
     * @Then /^I should see the (\d+) most-recent annual reports in the 'Latest annual reports' list$/
     */
    public function iShouldSeeTheMostRecentAnnualReportsInTheLatestAnnualReportsList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest annual reports") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedYear = ($this->numberOfAnnualReports - $nthChild + 2012);

                $this->assertSession()->elementContains(
                    'css',
                    ".list-heading:contains(\"Latest\") + .listing-list > .listing-list__item:nth-child($nthChild)",
                    "$expectedYear Annual Report"
                );
            }
        });
    }
}
