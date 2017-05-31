<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class CommunityContext extends Context
{
    private $numberOfArticles;

    /**
     * @Given /^(\d+) Community articles have been published$/
     */
    public function communityArticlesHaveBeenPublished(int $number)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
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
                'published' => $today->format(ApiSdk::DATE_FORMAT),
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/community?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.community-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.community-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$articles[0]],
                ])
            )
        );

        foreach (array_chunk($articles, $chunk = 10) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/community?page=$page&per-page=$chunk&order=desc",
                    ['Accept' => 'application/vnd.elife.community-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.community-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $articleChunk,
                    ])
                )
            );
        }
    }

    /**
     * @When /^I go to the Community page$/
     */
    public function iGoToTheCommunityPage()
    {
        $this->visitPath('/community');
    }

    /**
     * @When /^I load more community content$/
     */
    public function iLoadMoreCommunityContent()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) Community articles in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestSectionArticlesInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest")', 1);
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Interview '.$expectedNumber.' title'
                );
            }
        });
    }
}
