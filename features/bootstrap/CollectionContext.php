<?php

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
                'updated' => $today->format(DATE_RFC3339),
                'image' => [
                    'banner' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                        ],
                    ],
                    'thumbnail' => [
                        'alt' => '',
                        'sizes' => [
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
                'selectedCurator' => [
                    'id' => "$i",
                    'type' => 'senior-editor',
                    'name' => [
                        'preferred' => 'Person '.$i,
                        'index' => $i.', Person',
                    ],
                ],
                'curators' => [
                    [
                        'id' => "$i",
                        'type' => 'senior-editor',
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
                        'published' => $today->format(DATE_RFC3339),
                    ],
                ],
            ];
        }

        foreach (array_chunk($collections, 6) as $i => $collectionsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/collections?page=$page&per-page=6&order=desc",
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
                        ['Accept' => 'application/vnd.elife.collection+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.collection+json; version=1'],
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
     * @Then /^I should see the (\d+) most-recently-updated collections in the 'Latest collections' list$/
     */
    public function iShouldSeeTheMostRecentlyUpdatedCollectionsInTheLatestCollectionsList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest collections") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfCollections - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest collections") + ol > li:nth-child('.$nthChild.')',
                'Collection '.$expectedNumber.' title'
            );
        }
    }
}
