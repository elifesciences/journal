<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class MagazineControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_magazine_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Magazine', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for=&page=1&per-page=6&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode',
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
                        'event' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );

        return '/magazine';
    }
}
