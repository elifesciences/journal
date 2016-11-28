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

        foreach (array_chunk($articles, 6) as $i => $articlesChunk) {
            $page = $i + 1;

            unset($articlesChunk['content']);

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/blog-articles?page=$page&per-page=6&order=desc",
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
                        ['Accept' => 'application/vnd.elife.blog-article+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.blog-article+json; version=1'],
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
     * @Then /^I should see the latest (\d+) blog articles in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestBlogArticlesInTheLatestList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest") + ol > li:nth-child('.$nthChild.')',
                'Blog article '.$expectedNumber.' title'
            );
        }
    }
}
