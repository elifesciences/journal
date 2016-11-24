<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class HomepageContext extends Context
{
    private $numberOfArticles;
    private $numberOfMagazineArticles;

    /**
     * @Given /^(\d+) articles have been published$/
     */
    public function articlesHaveBeenPublished(int $number)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

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
                'published' => $today->format(DATE_RFC3339),
                'versionDate' => $today->format(DATE_RFC3339),
                'statusDate' => $today->format(DATE_RFC3339),
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

        foreach (array_chunk($articles, 6) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=6&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study",
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
                            'research-article' => $this->numberOfArticles,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'event' => 0,
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
     * @Given /^the Magazine has (\d+) items$/
     */
    public function theMagazineHasItems(int $number)
    {
        $this->numberOfMagazineArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable('-1 day'))->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'type' => 'podcast-episode',
                'number' => $i,
                'title' => 'Podcast episode '.$i.' title',
                'published' => $today->format(DATE_RFC3339),
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

        foreach (array_chunk($articles, 7) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=7&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode",
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
                            'research-article' => 0,
                            'research-exchange' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'event' => 0,
                            'interview' => 0,
                            'labs-experiment' => 0,
                            'podcast-episode' => $this->numberOfMagazineArticles,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the homepage$/
     */
    public function iGoToTheHomepage()
    {
        $this->visitPath('/');
    }

    /**
     * @Then /^I should see the latest (\d+) articles in the 'Latest research' list$/
     */
    public function iShouldSeeTheLatestArticlesInTheLatestResearchList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest research") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest research") + ol > li:nth-child('.$nthChild.')',
                'Article '.str_pad($expectedNumber, 5, '0', STR_PAD_LEFT).' title'
            );
        }
    }

    /**
     * @Then /^I should see the latest (\d+) Magazine items in the 'Magazine' list$/
     */
    public function iShouldSeeTheLatestMagazineItemsInTheMagazineList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Magazine") + ol > li', $number + 1);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfMagazineArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Magazine") + ol > li:nth-child('.$nthChild.')',
                'Podcast episode '.$expectedNumber.' title'
            );
        }

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Magazine") + ol > li:nth-child('.($number + 1).')',
            'See more Magazine articles'
        );
    }
}
