<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class LabsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_empty_labs_page()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments?page=1&per-page=6&order=desc',
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

        $crawler = $client->request('GET', '/labs');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife Labs', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No experiments available.', $crawler->filter('main')->text());
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

        $client->request('GET', '/labs?page='.$page);

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
                            'http://api.elifesciences.org/labs-experiments?page=1&per-page=1&order=desc',
                            ['Accept' => 'application/vnd.elife.labs-experiment-list+json; version=1']
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

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments?page=1&per-page=6&order=desc',
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

        return '/labs';
    }
}
