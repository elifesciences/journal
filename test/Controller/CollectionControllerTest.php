<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use Traversable;

final class CollectionControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_a_collection_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Collection title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Collection Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Collection title: Collection sub-title | Collections | eLife', $crawler->filter('title')->text());
        $this->assertSame('/collections/1/collection-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/collections/1/collection-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Collection title: Collection sub-title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Collection impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Collection impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://placehold.it/1800x900', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1800', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('900', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
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
        return $this->stringProvider('/collections/1', '/collections/1/foo');
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_collection_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections/1',
                [
                    'Accept' => 'application/vnd.elife.collection+json; version=1',
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

        $client->request('GET', '/collections/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections/1',
                ['Accept' => 'application/vnd.elife.collection+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'subTitle' => 'Collection sub-title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
                                ],
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
                                '16:9' => [
                                    250 => 'https://placehold.it/250x141',
                                    500 => 'https://placehold.it/500x281',
                                ],
                                '1:1' => [
                                    70 => 'https://placehold.it/70x70',
                                    140 => 'https://placehold.it/140x140',
                                ],
                            ],
                        ],
                    ],
                    'impactStatement' => 'Collection impact statement',
                    'selectedCurator' => [
                        'id' => 'person',
                        'type' => 'senior-editor',
                        'name' => [
                            'preferred' => 'Person',
                            'index' => 'Person',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => 'senior-editor',
                            'name' => [
                                'preferred' => 'Person',
                                'index' => 'Person',
                            ],
                        ],
                    ],
                    'content' => [
                        [
                            'type' => 'blog-article',
                            'id' => '1',
                            'title' => 'Blog article title',
                            'published' => '2010-01-01T00:00:00Z',
                        ],
                    ],
                ])
            )
        );

        return '/collections/1/collection-title';
    }
}
