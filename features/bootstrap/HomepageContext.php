<?php

use eLife\ApiSdk\ApiSdk;
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
                'published' => $today->format(ApiSdk::DATE_FORMAT),
                'versionDate' => $today->format(ApiSdk::DATE_FORMAT),
                'statusDate' => $today->format(ApiSdk::DATE_FORMAT),
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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study',
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
                        'research-article' => $this->numberOfArticles,
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
                'published' => $today->format(ApiSdk::DATE_FORMAT),
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
     * @Given /^there is a collection called \'([^\']*)\'$/
     */
    public function thereIsACollectionCalled(string $name)
    {
        // Do nothin.
    }

    /**
     * @Given /^there is a cover linking to the \'([^\']*)\' collection$/
     */
    public function thereIsACoverLinkingToTheCollection(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'title' => $collectionName,
                            'image' => [
                                'alt' => '',
                                'sizes' => [
                                    '2:1' => [
                                        900 => 'https://placehold.it/900x450?item',
                                        1800 => 'https://placehold.it/1800x900?item',
                                    ],
                                ],
                            ],
                            'item' => [
                                'type' => 'collection',
                                'id' => $id,
                                'title' => $collectionName,
                                'published' => $today->format(ApiSdk::DATE_FORMAT),
                                'image' => [
                                    'banner' => [
                                        'alt' => '',
                                        'sizes' => [
                                            '2:1' => [
                                                900 => 'https://placehold.it/900x450?item',
                                                1800 => 'https://placehold.it/1800x900?item',
                                            ],
                                        ],
                                    ],
                                    'thumbnail' => [
                                        'alt' => '',
                                        'sizes' => [
                                            '16:9' => [
                                                250 => 'https://placehold.it/250x141?item',
                                                500 => 'https://placehold.it/500x281?item',
                                            ],
                                            '1:1' => [
                                                70 => 'https://placehold.it/70x70?item',
                                                140 => 'https://placehold.it/140x140?item',
                                            ],
                                        ],
                                    ],
                                ],
                                'selectedCurator' => [
                                    'id' => '1',
                                    'type' => 'senior-editor',
                                    'name' => [
                                        'preferred' => 'Person 1',
                                        'index' => '1, Person',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @Given /^there is a cover linking to the \'([^\']*)\' collection with a custom title and image$/
     */
    public function thereIsACoverLinkingToTheCollectionWithACustomTitleAndImage(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover',
                            'image' => [
                                'alt' => '',
                                'sizes' => [
                                    '2:1' => [
                                        900 => 'https://placehold.it/900x450?cover',
                                        1800 => 'https://placehold.it/1800x900?cover',
                                    ],
                                ],
                            ],
                            'item' => [
                                'type' => 'collection',
                                'id' => $id,
                                'published' => $today->format(ApiSdk::DATE_FORMAT),
                                'title' => $collectionName,
                                'selectedCurator' => [
                                    'id' => '1',
                                    'type' => 'senior-editor',
                                    'name' => [
                                        'preferred' => 'Person 1',
                                        'index' => '1, Person',
                                    ],
                                ],
                                'image' => [
                                    'banner' => [
                                        'alt' => '',
                                        'sizes' => [
                                            '2:1' => [
                                                900 => 'https://placehold.it/900x450?item',
                                                1800 => 'https://placehold.it/1800x900?item',
                                            ],
                                        ],
                                    ],
                                    'thumbnail' => [
                                        'alt' => '',
                                        'sizes' => [
                                            '16:9' => [
                                                250 => 'https://placehold.it/250x141?item',
                                                500 => 'https://placehold.it/500x281?item',
                                            ],
                                            '1:1' => [
                                                70 => 'https://placehold.it/70x70?item',
                                                140 => 'https://placehold.it/140x140?item',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @When /^I go to the homepage$/
     */
    public function iGoToTheHomepage()
    {
        $this->visitPath('/');
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('More articles');
    }

    /**
     * @Then /^I should see the latest (\d+) articles in the 'Latest research' list$/
     */
    public function iShouldSeeTheLatestArticlesInTheLatestResearchList(int $number)
    {
        $this->spin(function () use ($number) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest research") + .listing-list > .listing-list__item', $number);

            for ($i = $number; $i > 0; --$i) {
                $nthChild = ($number - $i + 1);
                $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

                $this->assertSession()->elementContains(
                    'css',
                    '.list-heading:contains("Latest research") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                    'Article '.str_pad($expectedNumber, 5, '0', STR_PAD_LEFT).' title'
                );
            }
        });
    }

    /**
     * @Then /^I should see the latest (\d+) Magazine items in the 'Magazine' list$/
     */
    public function iShouldSeeTheLatestMagazineItemsInTheMagazineList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Magazine") + .listing-list > .listing-list__item', $number + 1);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfMagazineArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                'Podcast episode '.$expectedNumber.' title'
            );
        }

        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Magazine") + .listing-list > .listing-list__item:nth-child('.($number + 1).')',
            'See more Magazine articles'
        );
    }

    /**
     * @Then /^I should see the \'([^\']*)\' cover in the carousel$/
     */
    public function iShouldSeeTheCoverInTheCarousel(string $name)
    {
        $this->assertSession()->elementAttributeContains('css', '.carousel-item__title_link', 'href', $this->createId($name));
    }

    /**
     * @Then /^I should see the title and image from the \'([^\']*)\' collection used in the \'([^\']*)\' cover$/
     */
    public function iShouldSeeTheTitleAndImageFromTheCollectionUsedInTheCover(string $collectionName, string $coverName)
    {
        $this->assertSession()->elementTextContains('css', '.carousel-item__title', $coverName);
        $this->assertSession()->elementAttributeContains('css', '.carousel-item__inner', 'data-low-res-image-source', 'https://placehold.it/900x450?item');
        $this->assertSession()->elementAttributeContains('css', '.carousel-item__inner', 'data-high-res-image-source', 'https://placehold.it/1800x900?item');
    }

    /**
     * @Then /^I should see the custom title and image used in the \'([^\']*)\' cover$/
     */
    public function iShouldSeeTheCustomTitleAndImageUsedInTheCover($arg1)
    {
        $this->assertSession()->elementTextContains('css', '.carousel-item__title', 'Cover');
        $this->assertSession()->elementAttributeContains('css', '.carousel-item__inner', 'data-low-res-image-source', 'https://placehold.it/900x450?cover');
        $this->assertSession()->elementAttributeContains('css', '.carousel-item__inner', 'data-high-res-image-source', 'https://placehold.it/1800x900?cover');
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
