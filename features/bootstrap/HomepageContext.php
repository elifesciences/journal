<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class HomepageContext extends Context
{
    private $numberOfArticles;

    /**
     * @Given /^(\d+) articles have been published$/
     */
    public function articlesHaveBeenPublished(int $number)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'status' => 'poa',
                'id' => "$i",
                'version' => 1,
                'type' => 'research-article',
                'doi' => '10.7554/eLife.'.$i,
                'title' => 'Article '.$i.' title',
                'published' => $today->format(DATE_RFC3339),
                'volume' => 5,
                'elocationId' => 'e'.$i,
                'copyright' => [
                    'license' => 'CC-BY-4.0',
                    'holder' => 'Author et al',
                    'statement' => 'Creative Commons Attribution License.',
                ],
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
                        'subjects' => [],
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
                            'labs-experiment' => 0,
                            'podcast-episode' => 0,
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
                'Article '.$expectedNumber.' title'
            );
        }
    }
}
