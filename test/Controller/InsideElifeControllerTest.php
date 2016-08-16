<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class InsideElifeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_inside_elife_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Inside eLife', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles?page=1&per-page=6&order=desc',
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

        return '/inside-elife';
    }
}
