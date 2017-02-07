<?php

use Behat\Gherkin\Node\TableNode;
use eLife\ApiSdk\ApiSdk;
use eLife\Journal\Controller\ArchiveController;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Bridge\PhpUnit\ClockMock;

final class ArchiveContext extends Context
{
    /** @var DateTimeImmutable */
    private $today;

    public function __construct()
    {
        ClockMock::register(ArchiveController::class);
    }

    /**
     * @Given /^today is (\d+) ([A-Za-z]+) (\d+)$/
     */
    public function todayIsApril(int $day, string $month, int $year)
    {
        $this->today = DateTimeImmutable::createFromFormat('Y-F-j H:i:s', "$year-$month-$day 00:00:00", new DateTimeZone('Z'));

        ClockMock::withClockMock((int) $this->today->format('U'));
    }

    /**
     * @Given /^there are articles with covers:$/
     */
    public function thereAreArticlesWithCovers(TableNode $table)
    {
        $covers = array_map(function (array $coverData) {
            $date = DateTimeImmutable::createFromFormat('j F Y H:i:s', $coverData['Published'].' 00:00:00', new DateTimeZone('Z'));

            return [
                'title' => $coverData['Cover text'],
                'image' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450?'.$this->createId($coverData['Article']),
                            '1800' => 'https://placehold.it/1800x900?'.$this->createId($coverData['Article']),
                        ],
                    ],
                ],
                'item' => [
                    'type' => 'blog-article',
                    'id' => $this->createId($coverData['Article']),
                    'title' => $coverData['Article'],
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                ],
                '_page_views' => (int) str_replace(',', '', $coverData['Page views']),
                '_year' => (int) $date->format('Y'),
                '_month' => (int) $date->format('n'),
                '_year_month' => $date->format('Y-m'),
            ];
        }, $table->getColumnsHash());

        usort($covers, function (array $a, array $b) {
            return $b['_page_views'] <=> $a['_page_views'];
        });

        $coverGroups = [];
        foreach ($covers as $cover) {
            $coverGroups[$cover['_year_month']][] = $cover;
        }

        foreach ($coverGroups as $key => $covers) {
            $startDate = DateTimeImmutable::createFromFormat('j Y-m H:i:s', "1 $key 00:00:00", new DateTimeZone('Z'));
            $endDate = DateTimeImmutable::createFromFormat(' j Y-m H:i:s', $startDate->format('t')." $key 23:59:59", new DateTimeZone('Z'))->format('Y-m-d');
            $startDate = $startDate->format('Y-m-d');

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/covers?page=1&per-page=4&sort=page-views&order=desc&start-date=$startDate&end-date=$endDate",
                    ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                    json_encode([
                        'total' => count($covers),
                        'items' => array_map(function (array $cover) {
                            unset($cover['_page_views']);
                            unset($cover['_year']);
                            unset($cover['_month']);
                            unset($cover['_year_month']);

                            return $cover;
                        }, array_slice($covers, 0, 4)),
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the monthly archive for (\d+)$/
     */
    public function iGoToTheMonthlyArchiveFor(int $year)
    {
        $this->visitPath('/archive/'.$year);
    }

    /**
     * @Then /^I should see archives for:$/
     */
    public function iShouldSeeArchivesFor(TableNode $table)
    {
        $this->assertSession()->elementsCount('css', '.grid-listing-heading:contains("Monthly archive") + .grid-listing > .grid-listing-item', count($table->getColumn(0)));

        foreach ($table->getColumn(0) as $i => $month) {
            $this->assertSession()->elementContains(
                'css',
                '.grid-listing-heading:contains("Monthly archive") + .grid-listing > .grid-listing-item:nth-child('.($i + 1).')',
                $month
            );
        }
    }

    /**
     * @Then /^I should see the image from the cover for "([^"]*)" in the archive for ([A-Za-z]+) (\d+)$/
     */
    public function iShouldSeeTheImageFromTheCoverForInTheArchiveForMarch(string $article, string $month, int $year)
    {
        $this->assertSession()->elementAttributeContains(
            'css',
            '.grid-listing-heading:contains("Monthly archive") + .grid-listing > .grid-listing-item:contains("'.$month.' '.$year.'") .block-link',
            'data-high-res-image-source',
            'https://placehold.it/1800x900?'.$this->createId($article)
        );
    }

    /**
     * @Then /^I should see the following cover articles for ([A-Za-z]+) (\d+):$/
     */
    public function iShouldSeeTheFollowingCoverArticlesForMarch2016(string $month, int $year, TableNode $table)
    {
        foreach ($table->getColumn(0) as $i => $cover) {
            $this->assertSession()->elementContains(
                'css',
                '.grid-listing-heading:contains("Monthly archive") + .grid-listing > .grid-listing-item:contains("'.$month.' '.$year.'") .archive-nav-link__sub_links_list_item:nth-child('.($i + 2).')',
                $cover
            );
        }
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
