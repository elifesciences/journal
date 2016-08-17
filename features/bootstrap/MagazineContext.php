<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class MagazineContext extends Context
{
    private $numberOfArticles;
    private $numberOfPodcastEpisodes;

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
                'type' => 'podcast-episode',
                'number' => $i,
                'title' => 'Podcast episode '.$i.' title',
                'published' => $today->format(DATE_RFC3339),
                'image' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
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
                'mp3' => 'https://www.example.com/episode'.$i.'.mp3',
            ];
        }

        foreach (array_chunk($articles, 6) as $i => $articleChunk) {
            $page = $i + 1;

            $this->mockApiResponse(
                new Request(
                    'GET',
                    "http://api.elifesciences.org/search?for=&page=$page&per-page=6&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode",
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
                            'research-article' => 0,
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
                            'podcast-episode' => $this->numberOfArticles,
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
                'published' => $today->format(DATE_RFC3339),
                'image' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
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
                'mp3' => 'https://example.com/episode'.$number.'.mp3',
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
                        'items' => $episodesChunk,
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
                    'items' => [$episodes[0]],
                ])
            )
        );
    }

    /**
     * @When /^I go to the Magazine page$/
     */
    public function iGoToTheMagazinePage()
    {
        $this->visitPath('/magazine');
    }

    /**
     * @Then /^I should see the latest (\d+) Magazine articles in the 'Latest' list$/
     */
    public function iShouldSeeTheLatestMagazineArticlesInTheLatestList(int $number)
    {
        $this->assertSession()->elementsCount('css', '.list-heading:contains("Latest") + ol > li', $number);

        for ($i = $number; $i > 0; --$i) {
            $nthChild = ($number - $i + 1);
            $expectedNumber = ($this->numberOfArticles - $nthChild + 1);

            $this->assertSession()->elementContains(
                'css',
                '.list-heading:contains("Latest") + ol > li:nth-child('.$nthChild.')',
                'Podcast episode '.$expectedNumber.' title'
            );
        }
    }

    /**
     * @Then /^I should be able to play the latest podcast episode$/
     */
    public function iShouldBeAbleToPlayTheLatestPodcastEpisode()
    {
        $this->assertSession()->elementExists('css', '.audio-player');
        $this->assertSession()
            ->elementContains('css', '.audio-player__header',
                'Latest podcast: Episode '.$this->numberOfPodcastEpisodes.' title')
        ;
        $this->assertSession()
            ->elementAttributeContains('css', '.audio-player__player source', 'src',
                'https://example.com/episode'.$this->numberOfPodcastEpisodes.'.mp3')
        ;
    }
}
