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
                'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=relevance&order=desc',
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
                        'event' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
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
