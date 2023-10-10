<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class CollectionContext extends Context
{
    private $numberOfCollections;

    /**
     * @Given /^there are (\d+) collections$/
     */
    public function thereAreCollections(int $number)
    {
        $this->numberOfCollections = $number;

        $collections = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $collections[] = [
                'id' => "$i",
                'title' => 'Collection '.$i.' title',
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'image' => [
                    'banner' => [
                        'uri' => "https://www.example.com/iiif/banner%2F$i",
                        'alt' => '',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => "https://www.example.com/banner$i.jpg",
                            'filename' => "banner$i.jpg",
                        ],
                        'size' => [
                            'width' => 1800,
                            'height' => 1600,
                        ],
                    ],
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
                'selectedCurator' => [
                    'id' => "$i",
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior editor',
                    ],
                    'name' => [
                        'preferred' => 'Person '.$i,
                        'index' => $i.', Person',
                    ],
                ],
                'curators' => [
                    [
                        'id' => "$i",
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person '.$i,
                            'index' => $i.', Person',
                        ],
                    ],
                ],
                'content' => [
                    [
                        'type' => 'blog-article',
                        'id' => "$i",
                        'title' => 'Blog article '.$i.' title',
                        'published' => $today->format(ApiSdk::DATE_FORMAT),
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.collection-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $collection) {
                        unset($collection['image']['banner']);
                        unset($collection['curators']);
                        unset($collection['content']);

                        return $collection;
                    }, [$collections[0]]),
                ])
            )
        );

        foreach (array_chunk($collections, $chunk = 10) as $i => $collectionsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/collections?page=$page&per-page=$chunk&order=desc",
                    ['Accept' => 'application/vnd.elife.collection-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.collection-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $collection) {
                            unset($collection['image']['banner']);
                            unset($collection['curators']);
                            unset($collection['content']);

                            return $collection;
                        }, $collectionsChunk),
                    ])
                )
            );

            foreach ($collectionsChunk as $collection) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/collections/'.$collection['id'],
                        ['Accept' => 'application/vnd.elife.collection+json; version=3']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                        json_encode($collection)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go the collections page$/
     */
    public function iGoTheCollectionsPage()
    {
        $this->visitPath('/collections');
    }

    /**
     * @When /^I load more collections/
     */
    public function iLoadMoreCollections()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the (\d+) most-recently-updated collections in the 'Latest collections' list$/
     */
    public function iShouldSeeTheMostRecentlyUpdatedCollectionsInTheLatestCollectionsList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfCollections - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Collection '.$expectedNumber.' title'
                );
            }
        });
    }
}
