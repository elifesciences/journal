<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class SubjectControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_a_subject_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Subject', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Subject | eLife', $crawler->filter('title')->text());
        $this->assertSame('/subjects/subject', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/subjects/subject', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Subject', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Subject impact statement.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Subject impact statement.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/banner/0,529,1800,543/1114,336/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1114', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('336', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_ignores_podcast_highlights()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/highlights/subject',
                ['Accept' => 'application/vnd.elife.highlights+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.highlights+json; version=1'],
                json_encode([
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
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item'));
        $this->assertContains('Article highlight', $crawler->filter('.list-heading:contains("Highlights") + .listing-list > .listing-list__item:nth-child(1)')->text());
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

        $client->request('GET', '/subjects/subject?page='.$page);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', 'foo'] as $page) {
            yield 'page '.$page => [$page];
        }

        foreach (['2'] as $page) {
            yield 'page '.$page => [
                $page,
                function () use ($page) {
                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/subjects/subject',
                            ['Accept' => 'application/vnd.elife.subject+json; version=1']
                        ),
                        new Response(
                            200,
                            ['Content-Type' => 'application/vnd.elife.subject+json; version=1'],
                            json_encode([
                                'id' => 'subject',
                                'name' => 'Subject',
                                'impactStatement' => 'Subject impact statement.',
                                'image' => [
                                    'banner' => [
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
                            ])
                        )
                    );

                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&subject[]=subject&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default',
                            ['Accept' => 'application/vnd.elife.search+json; version=1']
                        ),
                        new Response(
                            404,
                            ['Content-Type' => 'application/problem+json'],
                            json_encode(['title' => 'Not found'])
                        )
                    );
                },
            ];
        }
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_subject_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
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

        $client->request('GET', '/subjects/subject');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject',
                [
                    'Accept' => 'application/vnd.elife.subject+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.subject+json; version=1',
                ],
                json_encode([
                    'id' => 'subject',
                    'name' => 'Subject',
                    'impactStatement' => 'Subject impact statement.',
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
                ])
            )
        );

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&subject[]=subject&type[]=research-article&type[]=research-advance&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default',
                [
                    'Accept' => 'application/vnd.elife.search+json; version=1',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.search+json; version=1',
                ],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject',
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
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/subjects/subject';
    }
}
