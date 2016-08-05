<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class MagazineContext extends Context
{
    private $numberOfPodcastEpisodes;

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
