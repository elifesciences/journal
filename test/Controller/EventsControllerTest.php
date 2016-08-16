<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class EventsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_empty_events_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife events', $crawler->filter('.content-header__title')->text());
        $this->assertContains(
            'There are currently no pending events. Please call back soon.',
            $crawler->filter('main')->text()
        );
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/events?page=1&per-page=6&type=open&order=asc',
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

        return '/events';
    }
}
