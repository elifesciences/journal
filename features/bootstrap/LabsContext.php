<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class LabsContext extends Context
{
    private $numberOfPosts;

    /**
     * @Given /^there are (\d+) Labs posts$/
     */
    public function thereAreLabsPosts(int $number)
    {
        $this->numberOfPosts = $number;

        $posts = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $posts[] = [
                'id' => (string) $i,
                'title' => 'Post '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'thumbnail' => [
                        'uri' => "https://www.example.com/iiif/thumbnail%2F$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/thumbnail$i.jpg",
                            'filename' => "thumbnail$i.jpg",
                        ],
                        'size' => [
                            'width' => 800,
                            'height' => 600,
                        ],
                    ],
                ],
                'impactStatement' => 'Post '.$i.' impact statement',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Post '.$i.' text.',
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-post-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $post) {
                        unset($post['content']);

                        return $post;
                    }, [$posts[0]]),
                ])
            )
        );

        foreach (array_chunk($posts, 8) as $i => $postsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/labs-posts?page=$page&per-page=8&order=desc",
                    ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.labs-post-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $post) {
                            unset($post['content']);

                            return $post;
                        }, $postsChunk),
                    ])
                )
            );

            foreach ($postsChunk as $post) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/labs-posts/'.$post['id'],
                        ['Accept' => 'application/vnd.elife.labs-post+json; version=2']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.labs-post+json; version=2'],
                        json_encode($post)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go the Labs page$/
     */
    public function iGoTheLabsPage()
    {
        $this->visitPath('/labs');
    }

    /**
     * @When /^I load more posts$/
     */
    public function iLoadMorePosts()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) Labs posts in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestLabsPostsInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()
                ->elementsCount('css', '.list-heading:contains("Latest") + .grid-listing > .grid-listing-item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfPosts - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .grid-listing > .grid-listing-item:nth-child('.$nthChild.')',
                    'Post '.$expectedNumber.' title'
                );
            }
        });
    }
}
