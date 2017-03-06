<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

final class StatusTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_200_pong_when_the_application_is_correctly_setup()
    {
        $client = static::createClient();

        $client->request('GET', '/ping');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('text/plain; charset=UTF-8', $client->getResponse()->headers->get('Content-Type'));
        $this->assertSame('pong', $client->getResponse()->getContent());
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    /**
     * @test
     */
    public function it_returns_200_status_when_the_application_is_correctly_setup()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles?page=1&per-page=1&order=asc',
                ['Accept' => 'application/vnd.elife.article-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'status' => 'poa',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Title',
                            'stage' => 'published',
                            'published' => '2016-01-02T00:00:00Z',
                            'statusDate' => '2016-01-02T00:00:00Z',
                            'versionDate' => '2016-01-02T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC0-1.0',
                                'statement' => 'Statement.',
                            ],
                            'authorLine' => 'Foo Bar et al',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'Foo Bar',
                                        'index' => 'Bar, Foo',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.blog-article-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.blog-article-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.collection-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers?page=1&per-page=1&sort=date&order=desc&use-date=default',
                ['Accept' => 'application/vnd.elife.cover-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.cover-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/events?page=1&per-page=1&type=all&order=desc',
                ['Accept' => 'application/vnd.elife.event-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.event-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.interview-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.interview-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.labs-experiment-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-experiment-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/medium-articles?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.medium-article-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.medium-article-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/article/00001/page-views?by=month&page=1&per-page=20&order=desc',
                ['Accept' => 'application/vnd.elife.metric-time-period+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.metric-time-period+json; version=1'],
                json_encode([
                    'totalValue' => 0,
                    'totalPeriods' => 0,
                    'periods' => [],
                ])
            )
        );

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
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/recommendations/article/00001?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.recommendations+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.recommendations+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=relevance&order=desc&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page=1&per-page=1&order=desc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $crawler = $client->request('GET', '/status');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Everything is ok', $crawler->text());
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }

    /**
     * @test
     */
    public function it_returns_500_status_when_the_api_is_not_working()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/status');

        $this->assertSame(500, $client->getResponse()->getStatusCode());
        $this->assertContains('Everything is not ok', $crawler->text());
        $this->assertFalse($client->getResponse()->isCacheable());
        $this->assertSame('none', $client->getResponse()->headers->get('X-Robots-Tag'));
    }
}
