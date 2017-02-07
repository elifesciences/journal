<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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
        $this->assertSame('eLife community', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No community content available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_404_when_not_on_a_valid_page()
    {
        $client = static::createClient();

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

        $client->request('GET', '/community?page=2');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/community?page=1&per-page=6&order=desc',
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
