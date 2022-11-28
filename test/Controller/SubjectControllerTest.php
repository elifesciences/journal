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
        $this->assertSame('https://www.example.com/iiif/ban%2Fner/full/full/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1800', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('1600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('application/rss+xml', $crawler->filter('link[rel="alternate"]')->attr('type'));
        $this->assertSame('eLife Sciences Subject articles', $crawler->filter('link[rel="alternate"]')->attr('title'));
        $this->assertSame('/rss/subject/subject.xml', $crawler->filter('link[rel="alternate"]')->attr('href'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    /**
     * @test
     */
    public function it_has_a_view_selector_for_smaller_devices()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $tabSelector = $crawler->filter('.button--switch-selector .view-selector__link');
        $this->assertCount(1, $tabSelector);
        $this->assertEquals([
            [
                'Latest articles',
                '#primaryListing',
            ],
            [
                'Highlights',
                '#secondaryListing',
            ],
        ], $tabSelector->extract(['_text', 'href']));
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
                            'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&subject[]=subject&type[]=reviewed-preprint&type[]=research-article&type[]=research-communication&type[]=research-advance&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default',
                            ['Accept' => 'application/vnd.elife.search+json; version=2']
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

    /**
     * @test
     */
    public function it_redirects_if_the_subject_is_rewritten()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/new-subject',
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
                    'id' => 'new-subject',
                    'name' => 'New Subject',
                    'impactStatement' => 'Subject impact statement.',
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

        $client->followRedirects(false);

        $client->request('GET', '/subjects/old-subject');

        $this->assertTrue($client->getResponse()->isRedirect('/subjects/new-subject'));
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
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
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
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&subject[]=subject&type[]=reviewed-preprint&type[]=research-article&type[]=research-communication&type[]=research-advance&type[]=review-article&type[]=scientific-correspondence&type[]=short-report&type[]=tools-resources&type[]=replication-study&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&use-date=default',
                [
                    'Accept' => 'application/vnd.elife.search+json; version=2',
                ]
            ),
            new Response(
                200,
                [
                    'Content-Type' => 'application/vnd.elife.search+json; version=2',
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

        return '/subjects/subject';
    }
}
