<?php

use eLife\ApiSdk\ApiSdk;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class MagazineContext extends Context
{
    private $numberOfArticles;
    private $numberOfPodcastEpisodes;
    private $numberOfEvents;
    private $numberOfDigests;

    /**
     * @Given /^(\d+) Magazine articles have been published$/
     */
    public function magazineArticlesHaveBeenPublished(int $number)
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
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
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
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => $this->numberOfArticles,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ])
            )
        );

        foreach (array_chunk($articles, $chunk = 10) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=$chunk&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default",
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
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
                            'research-communication' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'review-article' => 0,
                            'scientific-correspondence' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => $this->numberOfArticles,
                            'labs-post' => 0,
                            'podcast-episode' => 0,
                            'reviewed-preprint' => 0,
                        ],
                    ])
                )
            );
        }
    }

    /**
     * @Given /^there are (\d+) podcast episodes$/
     */
    public function thereArePodcastEpisodes(int $number)
    {
        $this->numberOfPodcastEpisodes = $number;

        $episodes = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $episodes[] = [
                'number' => $i,
                'title' => 'Episode '.$i.' title',
                'impactStatement' => 'Episode '.$i.' impact statement',
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
                'sources' => [
                    [
                        'mediaType' => 'audio/mpeg',
                        'uri' => $this->locatePath('/tests/blank.mp3'),
                    ],
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'Chapter 1',
                        'time' => 0,
                    ],
                ],
            ];
        }

        foreach (array_chunk($episodes, $chunk = 10) as $i => $episodesChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/podcast-episodes?page=$page&per-page=$chunk&order=desc",
                    ['Accept' => 'application/vnd.elife.podcast-episode-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.podcast-episode-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $episode) {
                            unset($episode['chapters']);
                            unset($episode['image']['banner']);

                            return $episode;
                        }, $episodesChunk),
                    ])
                )
            );

            foreach ($episodesChunk as $episode) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/podcast-episodes/'.$episode['number'],
                        ['Accept' => 'application/vnd.elife.podcast-episode+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.podcast-episode+json; version=1'],
                        json_encode($episode)
                    )
                );
            }
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.podcast-episode-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.podcast-episode-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $episode) {
                        unset($episode['image']['banner']);
                        unset($episode['chapters']);

                        return $episode;
                    }, [$episodes[0]]),
                ])
            )
        );
    }

    /**
     * @Given /^there is a collection called \'([^\']*)\'$/
     */
    public function thereIsACollectionCalled(string $name)
    {
        // Do nothing.
    }

    /**
     * @Given /^there is a Magazine highlight linking to the \'([^\']*)\' collection$/
     */
    public function thereIsAMagazineHighlightLinkingToTheCollection(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/highlights/magazine?page=1&per-page=6&order=desc',
                ['Accept' => 'application/vnd.elife.highlight-list+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlight-list+json; version=3'],
                json_encode([
                    'total' => 3,
                    'items' => array_fill(0, 3, [
                        'title' => $collectionName,
                        'image' => [
                            'uri' => 'https://www.example.com/iiif/item%2Fimage',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/item-image.jpg',
                                'filename' => 'item-image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                        'item' => [
                            'type' => 'collection',
                            'id' => $id,
                            'title' => $collectionName,
                            'published' => $today->format(ApiSdk::DATE_FORMAT),
                            'image' => [
                                'thumbnail' => [
                                    'uri' => 'https://www.example.com/iiif/item%2Fimage',
                                    'alt' => '',
                                    'source' => [
                                        'mediaType' => 'image/jpeg',
                                        'uri' => 'https://www.example.com/item-image.jpg',
                                        'filename' => 'item-image.jpg',
                                    ],
                                    'size' => [
                                        'width' => 800,
                                        'height' => 600,
                                    ],
                                ],
                            ],
                            'selectedCurator' => [
                                'id' => '1',
                                'type' => [
                                    'id' => 'senior-editor',
                                    'label' => 'Senior editor',
                                ],
                                'name' => [
                                    'preferred' => 'Person 1',
                                    'index' => '1, Person',
                                ],
                            ],
                        ],
                    ]),
                ])
            )
        );
    }

    /**
     * @Given /^there is a Magazine highlight linking to the \'([^\']*)\' collection with a custom title and image$/
     */
    public function thereIsAMagazineHighlightLinkingToTheCollectionWithACustomTitleAndImage(string $collectionName)
    {
        $id = $this->createId($collectionName);

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/highlights/magazine?page=1&per-page=6&order=desc',
                ['Accept' => 'application/vnd.elife.highlight-list+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlight-list+json; version=3'],
                json_encode([
                    'total' => 3,
                    'items' => [
                            [
                                'title' => 'Highlight',
                                'image' => [
                                    'uri' => 'https://www.example.com/iiif/highlight%2Fimage',
                                    'alt' => '',
                                    'source' => [
                                        'mediaType' => 'image/jpeg',
                                        'uri' => 'https://www.example.com/highlight-image.jpg',
                                        'filename' => 'highlight-image.jpg',
                                    ],
                                    'size' => [
                                        'width' => 800,
                                        'height' => 600,
                                    ],
                                ],
                                'item' => [
                                    'type' => 'collection',
                                    'id' => $id,
                                    'title' => $collectionName,
                                    'published' => $today->format(ApiSdk::DATE_FORMAT),
                                    'image' => [
                                        'thumbnail' => [
                                            'uri' => 'https://www.example.com/iiif/item%2Fimage',
                                            'alt' => '',
                                            'source' => [
                                                'mediaType' => 'image/jpeg',
                                                'uri' => 'https://www.example.com/item-image.jpg',
                                                'filename' => 'item-image.jpg',
                                            ],
                                            'size' => [
                                                'width' => 800,
                                                'height' => 600,
                                            ],
                                        ],
                                    ],
                                    'selectedCurator' => [
                                        'id' => '1',
                                        'type' => [
                                            'id' => 'senior-editor',
                                            'label' => 'Senior editor',
                                        ],
                                        'name' => [
                                            'preferred' => 'Person 1',
                                            'index' => '1, Person',
                                        ],
                                    ],
                                ],
                            ],
                        ] + array_fill(1, 2, [
                            'title' => $collectionName,
                            'image' => [
                                'uri' => 'https://www.example.com/iiif/item%2Fimage',
                                'alt' => '',
                                'source' => [
                                    'mediaType' => 'image/jpeg',
                                    'uri' => 'https://www.example.com/item-image.jpg',
                                    'filename' => 'item-image.jpg',
                                ],
                                'size' => [
                                    'width' => 800,
                                    'height' => 600,
                                ],
                            ],
                            'item' => [
                                'type' => 'collection',
                                'id' => $id,
                                'title' => $collectionName,
                                'published' => $today->format(ApiSdk::DATE_FORMAT),
                                'image' => [
                                    'thumbnail' => [
                                        'uri' => 'https://www.example.com/iiif/item%2Fimage',
                                        'alt' => '',
                                        'source' => [
                                            'mediaType' => 'image/jpeg',
                                            'uri' => 'https://www.example.com/item-image.jpg',
                                            'filename' => 'item-image.jpg',
                                        ],
                                        'size' => [
                                            'width' => 800,
                                            'height' => 600,
                                        ],
                                    ],
                                ],
                                'selectedCurator' => [
                                    'id' => '1',
                                    'type' => [
                                        'id' => 'senior-editor',
                                        'label' => 'Senior editor',
                                    ],
                                    'name' => [
                                        'preferred' => 'Person 1',
                                        'index' => '1, Person',
                                    ],
                                ],
                            ],
                        ]),
                ])
            )
        );
    }

    /**
     * @Given /^there are no upcoming events$/
     */
    public function thereAreNoUpcomingEvents()
    {
        $this->thereAreUpcomingEvents(0);
    }

    /**
     * @Given /^there are (\d+) upcoming events$/
     */
    public function thereAreUpcomingEvents(int $number)
    {
        $this->numberOfEvents = $number;

        $events = [];

        $starts = (new DateTimeImmutable())->setTime(0, 0, 0);
        $ends = $starts->modify('+1 day');

        for ($i = $number; $i > 0; --$i) {
            $events[] = [
                'id' => "$i",
                'title' => 'Event '.$i.' title',
                'published' => '2010-01-01T00:00:00Z',
                'starts' => $starts->format(ApiSdk::DATE_FORMAT),
                'ends' => $ends->format(ApiSdk::DATE_FORMAT),
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Event '.$i.' text.',
                    ],
                ],
            ];
        }

        foreach (array_chunk($events, 3) as $i => $eventsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/events?page=$page&per-page=3&show=open&order=asc",
                    ['Accept' => 'application/vnd.elife.event-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.event-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $item) {
                            unset($item['content']);

                            return $item;
                        }, $eventsChunk),
                    ])
                )
            );

            foreach ($eventsChunk as $event) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        'http://api.elifesciences.org/events/'.$event['id'],
                        ['Accept' => 'application/vnd.elife.event+json; version=2']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.event+json; version=2'],
                        json_encode($event)
                    )
                );
            }
        }
    }

    /**
     * @Given /^there are no digests$/
     */
    public function thereAreNoDigests()
    {
        $this->thereAreDigests(0);
    }

    /**
     * @Given /^there are (\d+) digests$/
     */
    public function thereAreDigests(int $number)
    {
        $this->numberOfDigests = $number;

        $digests = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $digests[] = [
                'id' => "{$i}",
                'title' => "Digest {$i} title",
                'stage' => 'published',
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
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => "Digest {$i} content.",
                    ],
                ],
                'relatedContent' => [
                    [
                        'type' => 'research-article',
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '12345',
                        'version' => 1,
                        'doi' => '10.7554/eLife.12345',
                        'title' => 'Article 12345',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 5,
                        'elocationId' => 'e12345',
                        'authorLine' => 'Foo Bar',
                    ],
                ],
            ];
        }

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.digest-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.digest-list+json; version=1'],
                json_encode([
                    'total' => $number,
                    'items' => array_map(function (array $digest) {
                        unset($digest['content']);
                        unset($digest['relatedContent']);

                        return $digest;
                    }, count($digests) ? [$digests[0]] : []),
                ])
            )
        );

        foreach (array_chunk($digests, $chunk = 3) as $i => $digestsChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/digests?page={$page}&per-page={$chunk}&order=desc",
                    ['Accept' => 'application/vnd.elife.digest-list+json; version=1']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.digest-list+json; version=1'],
                    json_encode([
                        'total' => $number,
                        'items' => array_map(function (array $digest) {
                            unset($digest['content']);
                            unset($digest['relatedContent']);

                            return $digest;
                        }, $digestsChunk),
                    ])
                )
            );

            foreach ($digestsChunk as $digest) {
                $this->mockApiResponse(
                    new Request(
                        'GET',
                        "http://api.elifesciences.org/digests/{$digest['id']}",
                        ['Accept' => 'application/vnd.elife.digest+json; version=1']
                    ),
                    new Response(
                        200,
                        ['Content-Type' => 'application/vnd.elife.digest+json; version=1'],
                        json_encode($digest)
                    )
                );
            }
        }
    }

    /**
     * @When /^I go to the Magazine page$/
     */
    public function iGoToTheMagazinePage()
    {
        if (null === $this->numberOfArticles) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default',
                    ['Accept' => 'application/vnd.elife.search+json; version=2']
                ),
                new Response(
                    200,
                    ['Content-Type' => 'application/vnd.elife.search+json; version=2'],
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
                            'research-communication' => 0,
                            'retraction' => 0,
                            'registered-report' => 0,
                            'replication-study' => 0,
                            'review-article' => 0,
                            'scientific-correspondence' => 0,
                            'short-report' => 0,
                            'tools-resources' => 0,
                            'blog-article' => 0,
                            'collection' => 0,
                            'interview' => 0,
                            'labs-post' => 0,
                            'podcast-episode' => 0,
                            'reviewed-preprint' => 0,
                        ],
                    ])
                )
            );
        }

        $this->visitPath('/magazine');
    }

    /**
     * @When /^I load more articles$/
     */
    public function iLoadMoreArticles()
    {
        $this->getSession()->getPage()->clickLink('Load more');
    }

    /**
     * @Then /^I should see the latest (\d+) Magazine articles in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestMagazineArticlesInTheLatestList(int $number)
    {
        $this->spin(function () use ($number) {
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

    /**
     * @Then /^I should be able to play the latest podcast episode$/
     */
    public function iShouldBeAbleToPlayTheLatestPodcastEpisode()
    {
        $this->assertSession()->elementExists('css', '.audio-player');
        $this->assertSession()
            ->elementContains('css', '.audio-player__header',
                'Episode '.$this->numberOfPodcastEpisodes);
        $this->assertSession()
            ->elementAttributeContains('css', '.audio-player__player source', 'src',
                $this->locatePath('/tests/blank.mp3'));
    }

    /**
     * @Then /^I should see the \'([^\']*)\' Magazine highlight in the 'Highlights' list$/
     */
    public function iShouldSeeTheMagazineHighlightInTheList(string $name)
    {
        $this->assertSession()->elementAttributeContains('css', '.highlights__list .teaser__header_text_link', 'href', $this->createId($name));
    }

    /**
     * @Then /^I should see the title and image from the \'([^\']*)\' collection used in the Magazine highlight$/
     */
    public function iShouldSeeTheTitleAndImageFromTheCollectionUsedInTheMagazineHighlight(string $collectionName)
    {
        $this->assertSession()->elementTextContains('css', '.highlights__list .teaser__header', $collectionName);
        $this->assertSession()->elementAttributeContains('css', '.highlights__list img', 'src', 'https://www.example.com/iiif/item%2Fimage/100,0,600,600/72,72/0/default.jpg');
    }

    /**
     * @Then /^I should see the custom title and image used in the \'([^\']*)\' Magazine highlight$/
     */
    public function iShouldSeeTheCustomTitleAndImageUsedInTheMagazineHighlight(string $collectionName)
    {
        $this->assertSession()->elementTextContains('css', '.highlights__list .teaser__header', 'Highlight');
        $this->assertSession()->elementAttributeContains('css', '.highlights__list img', 'src', 'https://www.example.com/iiif/highlight%2Fimage/100,0,600,600/72,72/0/default.jpg');
    }

    /**
     * @Then /^I should not see the 'Upcoming events' list$/
     */
    public function iShouldNotSeeTheUpcomingEventsList()
    {
        $this->assertSession()->elementNotExists('css', '.list-heading:contains("Events")');
    }

    /**
     * @Then /^I should see (\d+) upcoming events in the 'Events' list$/
     */
    public function iShouldSeeUpcomingEventsInTheEventsList(int $number)
    {
        if ($this->numberOfEvents > 3) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Events") + .listing-list > .listing-list__item', $number + 1);
        } else {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Events") + .listing-list > .listing-list__item', $number);
        }

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfEvents - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Events") + .listing-list > .listing-list__item:nth-child('.$nthChild.')',
                'Event '.$expectedNumber.' title'
            );
        }
    }

    /**
     * @Given /^I should not see a 'See more events' link$/
     */
    public function iShouldNotSeeASeeMoreEventsLink()
    {
        $this->assertSession()->elementNotExists('css', '.list-heading:contains("Events") + .listing-list > .listing-list__item:nth-child(4)');
        $this->assertSession()->elementTextNotContains(
            'css',
            '.list-heading:contains("Events") + .listing-list',
            'See more events'
        );
    }

    /**
     * @Given /^I should see a 'See more events' link$/
     */
    public function iShouldSeeASeeMoreEventsLink()
    {
        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Events") + .listing-list > .listing-list__item:nth-child(4)',
            'See more events'
        );
    }

    /**
     * @Then /^I should not see the 'Digests' list$/
     */
    public function iShouldNotSeeTheDigestsList()
    {
        $this->assertSession()->elementNotExists('css', '.list-heading:contains("Digests")');
    }

    /**
     * @Then /^I should see the latest (\d+) digests in the 'Digests' list$/
     */
    public function iShouldSeeTheLatestDigestsInTheDigestsList(int $number)
    {
        if ($this->numberOfDigests > 3) {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Digests") + .listing-list > .listing-list__item', $number + 1);
        } else {
            $this->assertSession()->elementsCount('css', '.list-heading:contains("Digests") + .listing-list > .listing-list__item', $number);
        }

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfDigests - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                ".list-heading:contains('Digests') + .listing-list > .listing-list__item:nth-child({$nthChild})",
                "Digest {$expectedNumber} title"
            );
        }
    }

    /**
     * @Then /^I should not see a 'See more digests' link$/
     */
    public function iShouldNotSeeASeeMoreDigestsLink()
    {
        $this->assertSession()->elementNotExists('css', '.list-heading:contains("Digests") + .listing-list > .listing-list__item:nth-child(4)');
        $this->assertSession()->elementTextNotContains(
            'css',
            '.list-heading:contains("Digests") + .listing-list',
            'See more digests'
        );
    }

    /**
     * @Then /^I should see a 'See more digests' link$/
     */
    public function iShouldSeeASeeMoreDigestsLink()
    {
        $this->assertSession()->elementContains(
            'css',
            '.list-heading:contains("Digests") + .listing-list > .listing-list__item:nth-child(4)',
            'See more digests'
        );
    }

    private function createId(string $name) : string
    {
        return md5($name);
    }
}
