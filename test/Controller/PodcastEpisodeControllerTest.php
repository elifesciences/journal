<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;

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
        $this->assertSame('Peer reviewed', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Episode title | Podcast | eLife', $crawler->filter('title')->text());
        $this->assertSame('/podcast/episode1', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/podcast/episode1', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Episode title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Episode impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Episode impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/ban%2Fner/full/,2000/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1636', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('2000', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('https://www.example.com/episode1.mp3', $crawler->filter('meta[property="og:audio:url"]')->attr('content'));
        $this->assertSame('audio/mpeg', $crawler->filter('meta[property="og:audio:type"]')->attr('content'));
        $this->assertSame('podcast-episode/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Episode title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 eLife Sciences Publications Limited. This article is distributed under the terms of the Creative Commons Attribution License, which permits unrestricted use and redistribution provided that the original author and source are credited.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_has_schema_org_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $script = $crawler->filter('script[type="application/ld+json"]');
        $this->assertNotEmpty($script);

        $value = $script->text();
        $this->assertJson($value);

        $this->markTestIncomplete('This test fails if schema.org is broken!');

        $graph = JsonLD::getDocument($value)->getGraph();
        $node = $graph->getNodes()[0];

        $this->assertEquals('http://schema.org/PodcastEpisode', $node->getType()->getId());
        $this->assertEquals(new TypedValue('Episode title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
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
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 2200,
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
