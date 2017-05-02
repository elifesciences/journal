<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class PodcastEpisodeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_podcast_episode_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Episode title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Podcast Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Episode 1: Episode title | Podcast | eLife', $crawler->filter('title')->text());
        $this->assertSame('/podcast/episode1', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/podcast/episode1', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Episode 1: Episode title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Episode impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Episode impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/banner/0,529,1800,543/1114,336/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1114', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('336', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('https://www.example.com/episode1.mp3', $crawler->filter('meta[property="og:audio:url"]')->attr('content'));
        $this->assertSame('audio/mpeg', $crawler->filter('meta[property="og:audio:type"]')->attr('content'));
    }

    /**
     * /**
     * @test
     */
    public function it_displays_a_404_if_the_episode_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes/2',
                ['Accept' => 'application/vnd.elife.podcast-episode+json; version=1']
            ),
            new Response(
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );

        $client->request('GET', '/podcast/episode2');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes/1',
                ['Accept' => 'application/vnd.elife.podcast-episode+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.podcast-episode+json; version=1'],
                json_encode([
                    'number' => 1,
                    'title' => 'Episode <i>title</i>',
                    'published' => '2010-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/banner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
                            ],
                        ],
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'impactStatement' => 'Episode impact <a href="#">statement</a>',
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://www.example.com/episode1.mp3',
                        ],
                    ],
                    'chapters' => [
                        [
                            'number' => 1,
                            'title' => 'Chapter 1',
                            'time' => 0,
                        ],
                    ],
                ])
            )
        );

        return '/podcast/episode1';
    }
}
