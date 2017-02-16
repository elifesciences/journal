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
    private $emptyResearchArticles;

    public function __construct()
    {
        ClockMock::register(ArchiveController::class);
    }

    /**
     * @BeforeScenario
     */
    public function resetEmptyResearchArticles()
    {
        $this->emptyResearchArticles = true;
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
                    "http://api.elifesciences.org/covers?page=1&per-page=4&sort=page-views&order=desc&use-date=published&start-date=$startDate&end-date=$endDate",
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
     * @Given /^(\d+) research articles were published during ([A-Za-z]+) (\d+)$/
     */
    public function researchArticlesWerePublishedDuringMarch(int $number, string $month, int $year)
    {
        $this->emptyResearchArticles = false;

        $date = DateTimeImmutable::createFromFormat('j F Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));

        $articles = [];
        for ($i = $number; $i > 0; --$i) {
            $i = str_pad($i, 5, '0', STR_PAD_LEFT);
            $articles[] = [
                'status' => 'poa',
                'stage' => 'published',
                'id' => "$i",
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.'.$i,
                'title' => 'Article '.$i.' title',
                'published' => $date->format(ApiSdk::DATE_FORMAT),
                'versionDate' => $date->format(ApiSdk::DATE_FORMAT),
                'statusDate' => $date->format(ApiSdk::DATE_FORMAT),
                'volume' => 5,
                'elocationId' => 'e'.$i,
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Author et al',
                    'statement' => 'Creative Commons Attribution License.',
                ],
                'authorLine' => 'Foo Bar',
            ];
        }

        $endDate = DateTimeImmutable::createFromFormat('j F Y H:i:s', $date->format('t')." $month $year 23:59:59", new DateTimeZone('Z'))->format('Y-m-d');
        $startDate = $date->format('Y-m-d');

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=publication&start-date=$startDate&end-date=$endDate",
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$articles[0]],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Some subject',
                            'results' => 0,
                        ],
                    ],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => $number,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, 100) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=100&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=publication&start-date=$startDate&end-date=$endDate",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
                        'subjects' => [
                            [
                                'id' => 'subject',
                                'name' => 'Some subject',
                                'results' => 0,
                            ],
                        ],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => $number,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^(\d+) Magazine articles were published during ([A-Za-z]+) (\d+)$/
     */
    public function magazineArticlesWerePublishedDuringMarch(int $number, string $month, int $year)
    {
        $date = DateTimeImmutable::createFromFormat('j F Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));

        $articles = [];
        for ($i = $number; $i > 0; --$i) {
            $i = str_pad($i, 5, '0', STR_PAD_LEFT);
            $articles[] = [
                'type' => 'interview',
                'id' => 'interview'.$i,
                'interviewee' => [
                    'name' => [
                        'preferred' => 'Interviewee '.$i,
                        'index' => 'Interviewee '.$i,
                    ],
                ],
                'title' => 'Interview '.$i.' title',
                'published' => $date->format(ApiSdk::DATE_FORMAT),
            ];
        }

        $endDate = DateTimeImmutable::createFromFormat('j F Y H:i:s', $date->format('t')." $month $year 23:59:59", new DateTimeZone('Z'))->format('Y-m-d');
        $startDate = $date->format('Y-m-d');

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default&start-date=$startDate&end-date=$endDate",
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$articles[0]],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => $number,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, 100) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=100&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default&start-date=$startDate&end-date=$endDate",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
                        'subjects' => [],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => $number,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are Magazine articles:$/
     */
    public function thereAreMagazineArticles(TableNode $table)
    {
        //$date = DateTimeImmutable::createFromFormat('j F Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));

        $articles = array_map(function (array $data) {
            $date = DateTimeImmutable::createFromFormat('j F Y H:i:s', $data['Published'].' 00:00:00', new DateTimeZone('Z'));
            $id = $this->createId($data['Article']);

            switch ($type = $data['Type']) {
                case 'Insight':
                    return [
                        'status' => 'poa',
                        'stage' => 'published',
                        'id' => $id,
                        'version' => 1,
                        'type' => 'research-article',
                        'doi' => "10.7554/eLife.$id",
                        'title' => $data['Article'],
                        'published' => $date->format(ApiSdk::DATE_FORMAT),
                        'versionDate' => $date->format(ApiSdk::DATE_FORMAT),
                        'statusDate' => $date->format(ApiSdk::DATE_FORMAT),
                        'volume' => 5,
                        'elocationId' => "e$id",
                        'copyright' => [
                            'license' => 'CC-BY-4.0',
                            'holder' => 'Author et al',
                            'statement' => 'Creative Commons Attribution License.',
                        ],
                        'authorLine' => 'Foo Bar',
                    ];
                case 'Podcast episode':
                    static $podcastNumber = 0;

                    ++$podcastNumber;

                    return [
                        'type' => 'podcast-episode',
                        'number' => $podcastNumber,
                        'title' => $data['Article'],
                        'published' => $date->format(ApiSdk::DATE_FORMAT),
                        'image' => [
                            'thumbnail' => [
                                'alt' => '',
                                'sizes' => [
                                    '16:9' => [
                                        '250' => 'https://placehold.it/250x141',
                                        '500' => 'https://placehold.it/500x281',
                                    ],
                                    '1:1' => [
                                        '70' => 'https://placehold.it/70x70',
                                        '140' => 'https://placehold.it/140x140',
                                    ],
                                ],
                            ],
                        ],
                        'sources' => [
                            [
                                'mediaType' => 'audio/mpeg',
                                'uri' => $this->locatePath('/audio-file'),
                            ],
                        ],
                    ];
            }

            throw new UnexpectedValueException('Unknown type'.$type);
        }, $table->getColumnsHash());

        usort($articles, function (array $a, array $b) {
            return $b['published'] <=> $a['published'];
        });

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default&start-date=2016-03-01&end-date=2016-03-31',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => count($articles),
                    'items' => [$articles[0]],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => array_reduce($articles, function (int $carry, array $article) {
                            if ('insight' === $article['type']) {
                                ++$carry;
                            }

                            return $carry;
                        }, 0),
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => array_reduce($articles, function (int $carry, array $article) {
                            if ('podcast-episode' === $article['type']) {
                                ++$carry;
                            }

                            return $carry;
                        }, 0),
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, 100) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=100&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default&start-date=2016-03-01&end-date=2016-03-31",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => count($articles),
                        'items' => $articleChunk,
                        'subjects' => [],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => array_reduce($articles, function (int $carry, array $article) {
                                if ('insight' === $article['type']) {
                                    ++$carry;
                                }

                                return $carry;
                            }, 0),
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => array_reduce($articles, function (int $carry, array $article) {
                                if ('podcast-episode' === $article['type']) {
                                    ++$carry;
                                }

                                return $carry;
                            }, 0),
                        ],
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
     * @When /^I go to the archive for ([A-Za-z]+) (\d+)$/
     */
    public function iGoToTheArchiveForMarch(string $month, int $year)
    {
        if ($this->emptyResearchArticles) {
            $date = DateTimeImmutable::createFromFormat('j F Y H:i:s', "1 $month $year 00:00:00", new DateTimeZone('Z'));
            $endDate = DateTimeImmutable::createFromFormat('j F Y H:i:s', $date->format('t')." $month $year 23:59:59", new DateTimeZone('Z'))->format('Y-m-d');
            $startDate = $date->format('Y-m-d');

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default&start-date=$startDate&end-date=$endDate",
                    ['Accept' => 'application/vnd.elife.search+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                    json_encode([
                        'total' => 0,
                        'items' => [],
                        'subjects' => [],
                        'types' => [
                            'correction' => 0,
                            'editorial' => 0,
                            'feature' => 0,
                            'insight' => 0,
                            'research-advance' => 0,
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
                        ],
                    ])
                )
            );
        }

        $this->visitPath('/archive/'.$year.'/'.strtolower($month));
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
     * @Then /^I should see the image from the cover for "([^"]*)" in the header$/
     */
    public function iShouldSeeTheImageFromTheCoverForInTheHeader(string $article)
    {
        $this->assertSession()->elementAttributeContains(
            'css',
            '.content-header',
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

    /**
     * @Then /^I should see the (\d+) research articles published during ([A-Za-z]+) (\d+) in the 'Research articles' list$/
     */
    public function iShouldSeeTheResearchArticlesPublishedDuringMarchInTheList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Research articles") + .listing-list > .listing-list__item', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($number - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Research articles") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                'Article '.str_pad($expectedNumber, 5, '0', STR_PAD_LEFT).' title'
            );
        }
    }

    /**
     * @Then /^I should see the (\d+) Magazine items published during ([A-Za-z]+) (\d+) in the 'Magazine' list$/
     */
    public function iShouldSeeTheMagazineItemsPublishedDuringMarchInTheList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Magazine") + .listing-list > .listing-list__item', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($number - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                'Interview '.str_pad($expectedNumber, 5, '0', STR_PAD_LEFT).' title'
            );
        }
    }

    /**
     * @Then /^I should see the "([^"]*)" at the top of the 'Magazine' list$/
     */
    public function iShouldSeeTheAtTheTopOfTheList(string $article)
    {
        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child(1)',
            $article
        );
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
