<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class CommunityControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_community_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Community', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No community articles available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_highlights()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/highlights/community?page=1&per-page=6&order=desc',
                ['Accept' => 'application/vnd.elife.highlight-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlight-list+json; version=1'],
                json_encode([
                    'total' => 3,
                    'items' => [
                        [
                            'title' => 'Article highlight',
                            'item' => [
                                'status' => 'vor',
                                'stage' => 'preview',
                                'id' => '00001',
                                'version' => 1,
                                'type' => 'research-article',
                                'doi' => '10.7554/eLife.00001',
                                'title' => 'Article',
                                'volume' => 1,
                                'elocationId' => 'e00001',
                                'copyright' => [
                                    'license' => 'CC-BY-4.0',
                                    'holder' => 'Bar',
                                    'statement' => 'Copyright statement.',
                                ],
                                'subjects' => [
                                    [
                                        'id' => 'subject',
                                        'name' => 'Subject',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Podcast episode highlight',
                            'item' => [
                                'type' => 'podcast-episode',
                                'number' => 1,
                                'title' => 'Podcast episode',
                                'published' => '2000-01-01T00:00:00Z',
                                'image' => [
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
                                'sources' => [
                                    [
                                        'mediaType' => 'audio/mpeg',
                                        'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3',
                                    ],
                                ],
                                'subjects' => [
                                    [
                                        'id' => 'subject',
                                        'name' => 'Subject',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Podcast episode chapter highlight',
                            'item' => [
                                'type' => 'podcast-episode-chapter',
                                'episode' => [
                                    'number' => 1,
                                    'title' => 'Podcast episode',
                                    'published' => '2000-01-01T00:00:00Z',
                                    'image' => [
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
                                    'sources' => [
                                        [
                                            'mediaType' => 'audio/mpeg',
                                            'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.06.mp3',
                                        ],
                                    ],
                                    'subjects' => [
                                        [
                                            'id' => 'subject',
                                            'name' => 'Subject',
                                        ],
                                    ],
                                ],
                                'chapter' => [
                                    'number' => 1,
                                    'title' => 'Chapter',
                                    'time' => 0,
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(3, $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item'));
        $this->assertContains('Article highlight', $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(1)')->text());
        $this->assertContains('Podcast episode highlight', $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(2)')->text());
        $this->assertContains('Podcast episode chapter highlight', $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(3)')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Community | eLife', $crawler->filter('title')->text());
        $this->assertSame('/community', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/community', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Community', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('The eLife community is working to help address some of the pressures on early-career scientists in a number of ways. Learn more about our work and advisory group, sign up for our bi-monthly news, follow us on Twitter, and explore recent activities below.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('The eLife community is working to help address some of the pressures on early-career scientists in a number of ways. Learn more about our work and advisory group, sign up for our bi-monthly news, follow us on Twitter, and explore recent activities below.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('assets.packages')->getUrl('assets/images/banners/community-1114x359.jpg'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1114', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('359', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    /**
     * @test
     * @dataProvider invalidPageProvider
     */
    public function it_displays_a_404_when_not_on_a_valid_page($page, callable $callable = null)
    {
        $client = static::createClient();

        if ($callable) {
            $callable();
        }

        $client->request('GET', "/community?page=$page");

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', 'foo'] as $page) {
            yield "page $page" => [$page];
        }

        foreach (['2'] as $page) {
            yield "page $page" => [
                $page,
                function () use ($page) {
                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/community?page=1&per-page=1&order=desc',
                            ['Accept' => 'application/vnd.elife.community-list+json; version=1']
                        ),
                        new Response(
                            200,
                            ['Content-Type' => 'application/vnd.elife.community-list+json; version=1'],
                            json_encode([
                                'total' => 0,
                                'items' => [],
                            ])
                        )
                    );
                },
            ];
        }
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/community?page=1&per-page=10&order=desc',
                ['Accept' => 'application/vnd.elife.community-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.community-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/community';
    }
}
