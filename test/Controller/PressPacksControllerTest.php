<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class PressPacksControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_press_packs_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('For the press', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No press packs available.', $crawler->filter('main')->text());
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

        $client->request('GET', "/for-the-press?page=$page");

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', 'foo'] as $page) {
            yield "page $page" => [$page];
        }

        foreach (['2'] as $page) {
            yield "page $page" => [
                $page,
                function () use ($page) {
                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/press-packages?page=1&per-page=1&order=desc',
                            ['Accept' => 'application/vnd.elife.press-package-list+json; version=1']
                        ),
                        new Response(
                            200,
                            ['Content-Type' => 'application/vnd.elife.press-package-list+json; version=1'],
                            json_encode([
                                'total' => 0,
                                'items' => [],
                            ])
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
                'http://api.elifesciences.org/press-packages?page=1&per-page=6&order=desc',
                ['Accept' => 'application/vnd.elife.press-package-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/for-the-press';
    }
}
