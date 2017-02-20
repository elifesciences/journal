<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class PressPackControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_a_press_pack_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Press package title', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     * @dataProvider incorrectSlugProvider
     */
    public function it_redirects_if_the_slug_is_not_correct(string $url)
    {
        $client = static::createClient();

        $expectedUrl = $this->getUrl();

        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect($expectedUrl));
    }

    public function incorrectSlugProvider() : Traversable
    {
        return $this->stringProvider('/for-the-press/1', '/for-the-press/1/foo');
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_press_pack_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages/1',
                [
                    'Accept' => 'application/vnd.elife.press-package+json; version=1',
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

        $client->request('GET', '/for-the-press/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages/1',
                ['Accept' => 'application/vnd.elife.press-package+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Press package title',
                    'published' => '2010-01-01T00:00:00Z',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Press package text.',
                        ],
                    ],
                    'relatedContent' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                        ],
                    ],
                ])
            )
        );

        return '/for-the-press/1/press-package-title';
    }
}
