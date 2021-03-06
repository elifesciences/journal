<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class BlogContext extends Context
{
    private $numberOfArticles;

    /**
     * @Given /^there are (\d+) blog articles$/
     */
    public function thereAreBlogArticles(int $number)
    {
        $this->numberOfArticles = $number;

        $articles = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $articles[] = [
                'id' => "$i",
                'title' => 'Blog article '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'impactStatement' => 'Blog article '.$i.' impact statement',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Experiment '.$i.' text.',
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.blog-article-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.blog-article-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => [$articles[0]],
                ])
            )
        );

        foreach (array_chunk($articles, $chunk = 10) as $i => $articlesChunk) {
            $page = $i + 1;

            unset($articlesChunk['content']);

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/blog-articles?page=$page&per-page=$chunk&order=desc",
                    ['Accept' => 'application/vnd.elife.blog-article-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.blog-article-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => $articlesChunk,
                    ])
                )
            );

            foreach ($articlesChunk as $article) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/blog-articles/'.$article['id'],
                        ['Accept' => 'application/vnd.elife.blog-article+json; version=2']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.blog-article+json; version=2'],
                        json_encode($article)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go the Inside eLife page$/
     */
    public function iGoTheInsideElifePage()
    {
        $this->visitPath('/inside-elife');
    }

    /**
     * @When /^I load more blog articles$/
     */
    public function iLoadMoreBlogArticles()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) blog articles in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestBlogArticlesInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Blog article '.$expectedNumber.' title'
                );
            }
        });
    }
}
