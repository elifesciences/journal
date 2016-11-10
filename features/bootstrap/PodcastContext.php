<?php

use Behat\Mink\Exception\ExpectationException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class PodcastContext extends Context
{
    private $numberOfEpisodes;

    /**
     * @Given /^there are (\d+) podcast episodes$/
     */
    public function thereArePodcastEpisodes(int $number)
    {
        $this->numberOfEpisodes = $number;

        $episodes = [];

        $today = (new DateTimeImmutable())->setTime(0, 0, 0);

        for ($i = $number; $i > 0; --$i) {
            $episodes[] = [
                'number' => $i,
                'title' => 'Episode '.$i.' title',
                'published' => $today->format(DATE_RFC3339),
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
                'impactStatement' => 'Experiment '.$i.' impact statement',
                'sources' => [
                    [
                        'mediaType' => 'audio/mpeg',
                        'uri' => $this->locatePath('/audio-file'),
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

        foreach (array_chunk($episodes, 6) as $i => $episodesChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/podcast-episodes?page=$page&per-page=6&order=desc",
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
    }

    /**
     * @Given /^there is a podcast episode with two chapters$/
     */
    public function thereIsAPodcastEpisodeWithTwoChapters()
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes/100',
                ['Accept' => 'application/vnd.elife.podcast-episode+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.podcast-episode+json; version=1'],
                json_encode([
                    'number' => 100,
                    'title' => 'Episode title',
                    'published' => date(DATE_RFC3339),
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
                    'impactStatement' => 'Experiment impact statement',
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => $this->locatePath('/audio-file'),
                        ],
                    ],
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter 1',
                            'time' => 3,
                            'content' => [
                                [
                                    'type' => 'research-article',
                                    'status' => 'vor',
                                    'id' => '12345',
                                    'version' => 1,
                                    'doi' => '10.7554/eLife.12345',
                                    'title' => 'Article 12345',
                                    'published' => '2010-01-01T00:00:00+00:00',
                                    'statusDate' => '2010-01-01T00:00:00+00:00',
                                    'volume' => 5,
                                    'elocationId' => 'e12345',
                                    'authorLine' => 'Foo Bar',
                                ],
                            ],
                        ],
                        [
                            'number' => 2,
                            'title' => 'Chapter 2',
                            'time' => 7,
                            'content' => [
                                [
                                    'type' => 'research-article',
                                    'status' => 'vor',
                                    'id' => '12346',
                                    'version' => 1,
                                    'doi' => '10.7554/eLife.12346',
                                    'title' => 'Article 12346',
                                    'published' => '2010-01-01T00:00:00+00:00',
                                    'statusDate' => '2010-01-01T00:00:00+00:00',
                                    'volume' => 5,
                                    'elocationId' => 'e12346',
                                    'authorLine' => 'Foo Bar',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }

    /**
     * @When /^I go to the podcast page$/
     */
    public function iGoToThePodcastPage()
    {
        $this->visitPath('/podcast');
    }

    /**
     * @When /^I go the podcast episode page$/
     */
    public function iGoThePodcastEpisodePage()
    {
        $this->visitPath('/podcast/episode100');

        if ($this->isJavaScript()) {
            $this->spin(function () {
                if (!$this->getSession()
                    ->evaluateScript('document.querySelector(".audio-player__player").readyState >= 2')
                ) {
                    throw new ExpectationException('Podcast is not loaded', $this->getSession()->getDriver());
                }
            });
        }
    }

    /**
     * @When /^I click on the second chapter's title$/
     */
    public function iClickOnTheSecondChaptersTitle()
    {
        $this->spin(function () {
            $this->assertSession()
                ->elementExists('css',
                    '.list-heading:contains("Chapters") + .listing-list > .listing-list__item:nth-child(2) .teaser__header_text_link');
        });

        $this->getSession()
            ->getPage()
            ->find('css',
                '.list-heading:contains("Chapters") + .listing-list > .listing-list__item:nth-child(2) .teaser__header_text_link')
            ->click();
    }

    /**
     * @Then /^I should see the latest (\d+) podcast episodes in the 'Latest episodes' list$/
     */
    public function iShouldSeeTheLatestPodcastEpisodesInTheEpisodesList(int $number)
    {
        $this->assertSession()
            ->elementsCount('css', '.grid-listing-heading:contains("Latest episodes") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfEpisodes - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.grid-listing-heading:contains("Latest episodes") + ol > li:nth-child('.$nthChild.')',
                'Episode '.$expectedNumber.' title'
            );
        }
    }

    /**
     * @Then /^I should see the two articles covered by the chapters in the 'Related' list$/
     */
    public function iShouldSeeTheTwoArticlesCoveredByTheChaptersInTheRelatedList()
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Related") + ol > li', 2);

        $this->assertSession()
            ->elementContains('css', '.list-heading:contains("Related") + ol > li:nth-child(1)', 'Article 12345')
        ;
        $this->assertSession()
            ->elementContains('css', '.list-heading:contains("Related") + ol > li:nth-child(2)', 'Article 12346')
        ;
    }

    /**
     * @Then /^the second chapter's number and title appear as part of the player title$/
     */
    public function theSecondChaptersNumberAndTitleAppearAsPartOfThePlayerTitle()
    {
        $this->spin(function () {
            $this->assertSession()->elementTextContains('css', '.audio-player__title', 'Episode 100: 2. Chapter 2');
        });
    }

    /**
     * @Then /^the audio player should start playing the second chapter$/
     */
    public function theAudioPlayerShouldStartPlayingTheSecondChapter()
    {
        $this->spin(function () {
            $chapter = $this->getSession()
                ->evaluateScript('document.querySelector(".current-chapter").dataset.chapterNumber');

            if (2 != $chapter) {
                throw new ExpectationException('Player is on chapter '.$chapter, $this->getSession()->getDriver());
            }

            if (true === $this->getSession()
                    ->evaluateScript('document.querySelector(".audio-player__player").paused')
            ) {
                throw new ExpectationException('Player is paused', $this->getSession()->getDriver());
            }
        });
    }

    /**
     * @Then /^there is an indication near the second chapter's title that this is the current chapter$/
     */
    public function thereIsAnIndicationNearTheSecondChaptersTitleThatThisIsTheCurrentChapter()
    {
        $this->spin(function () {
            $this->assertSession()
                ->elementAttributeContains('css',
                    '.list-heading:contains("Chapters") + .listing-list > .listing-list__item:nth-child(2) > .media-chapter-listing-item',
                    'class',
                    'current-chapter');
        });
    }
}
