<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class HomeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_homepage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());
    }

    /**
     * @test
     */
    public function it_displays_the_correct_dates_in_the_latest_research_list()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 2,
                    'items' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '2',
                            'version' => 2,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.2',
                            'title' => 'Article 2 title',
                            'published' => '2012-01-01T00:00:00Z',
                            'versionDate' => '2013-01-01T00:00:00Z',
                            'statusDate' => '2013-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e2',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author et al',
                                'statement' => 'Creative Commons Attribution License.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '1',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.1',
                            'title' => 'Article 1 title',
                            'published' => '2012-01-01T00:00:00Z',
                            'versionDate' => '2012-01-01T00:00:00Z',
                            'statusDate' => '2012-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e1',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author et al',
                                'statement' => 'Creative Commons Attribution License.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
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
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $teasers = $crawler->filter('.list-heading:contains("Latest research") + ol > li');
        $this->assertCount(2, $teasers);

        $this->assertSame('Article 2 title', trim($teasers->eq(0)->filter('.teaser__header_text')->text()));
        $this->assertSame('Research article Updated Jan 1, 2013', trim(preg_replace('/\s+/S', ' ', $teasers->eq(0)->filter('.teaser__footer .meta')->text())));

        $this->assertSame('Article 1 title', trim($teasers->eq(1)->filter('.teaser__header_text')->text()));
        $this->assertSame('Research article Jan 1, 2012', trim(preg_replace('/\s+/S', ' ', $teasers->eq(1)->filter('.teaser__footer .meta')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Latest research | eLife', $crawler->filter('title')->text());
        $this->assertSame('/', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Latest research', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_displays_the_homepage_even_if_the_api_is_unavailable()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                [
                    'Cache-Control' => 'public, max-age=0, stale-if-error=60',
                    'Content-Type' => 'application/vnd.elife.search+json; version=1',
                ],
                json_encode([
                    'total' => 0,
                    'items' => [],
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
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                503,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Service unavailable',
                ])
            )
        );

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
        $this->assertEmpty($client->getResponse()->headers->getCookies());
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

        $client->request('GET', '/?page='.$page);

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
                            'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
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
    public function it_displays_a_subjects_list()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=100&order=asc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => 'subject',
                            'name' => 'Subject name',
                            'impactStatement' => 'Subject impact statement.',
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
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame('Subject name', trim($crawler->filter('.section-listing__list_item')->text()));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&type[]=research-advance&type[]=research-article&type[]=research-exchange&type[]=short-report&type[]=tools-resources&type[]=replication-study&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
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
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/';
    }
}
