<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class LabsPostControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_a_labs_post_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Post title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Labs Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertContains('Post text.', $crawler->filter('main > div.wrapper')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Post title | Labs | eLife', $crawler->filter('title')->text());
        $this->assertSame('/labs/1/post-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/labs/1/post-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Post title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Post impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Post impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
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
        return $this->stringProvider('/labs/1', '/labs/1/foo');
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_post_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts/1',
                [
                    'Accept' => 'application/vnd.elife.labs-post+json; version=2',
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

        $client->request('GET', '/labs/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts/1',
                ['Accept' => 'application/vnd.elife.labs-post+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-post+json; version=2'],
                json_encode([
                    'id' => '1',
                    'title' => 'Post title',
                    'published' => '2010-01-01T00:00:00Z',
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://www.example.com/iiif/image',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/image.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 800,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'impactStatement' => 'Post impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Post text.',
                        ],
                    ],
                ])
            )
        );

        return '/labs/1/post-title';
    }
}
