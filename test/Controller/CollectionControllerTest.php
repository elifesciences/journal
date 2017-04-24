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

        $content = $crawler->filter('.list-heading:contains("Collection") + .listing-list > .listing-list__item');
        $this->assertCount(1, $content);
        $this->assertContains('Blog article title', $content->eq(0)->text());
    }

    /**
     * @test
     */
    public function it_displays_multimedia()
    {
        $client = static::createClient();

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
                    'podcastEpisodes' => [
                        [
                            'number' => 1,
                            'title' => 'Podcast episode title',
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
                            'sources' => [
                                [
                                    'mediaType' => 'audio/mpeg',
                                    'uri' => 'https://www.example.com/episode1.mp3',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/collections/1/collection-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $multimedia = $crawler->filter('.list-heading:contains("Multimedia") + .listing-list > .listing-list__item');
        $this->assertCount(1, $multimedia);
        $this->assertContains('Podcast episode title', $multimedia->eq(0)->text());
    }

    /**
     * @test
     */
    public function it_displays_related_content()
    {
        $client = static::createClient();

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
                            'title' => 'Blog article 1 title',
                            'published' => '2010-01-01T00:00:00Z',
                        ],
                    ],
                    'relatedContent' => [
                        [
                            'type' => 'blog-article',
                            'id' => '2',
                            'title' => 'Blog article 2 title',
                            'published' => '2010-01-01T00:00:00Z',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/collections/1/collection-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $related = $crawler->filter('.list-heading:contains("Related") + .listing-list > .listing-list__item');
        $this->assertCount(1, $related);
        $this->assertContains('Blog article 2 title', $related->eq(0)->text());
    }

    /**
     * @test
     */
    public function it_displays_contributors()
    {
        $client = static::createClient();

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
                    'impactStatement' => 'Collection impact statement',
                    'selectedCurator' => [
                        'id' => 'person',
                        'type' => 'senior-editor',
                        'name' => [
                            'preferred' => 'Person One',
                            'index' => 'Person One',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => 'reviewing-editor',
                            'name' => [
                                'preferred' => 'Person Two',
                                'index' => 'Person Two',
                            ],
                        ],
                        [
                            'id' => 'person',
                            'type' => 'senior-editor',
                            'name' => [
                                'preferred' => 'Person One',
                                'index' => 'Person One',
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

        $crawler = $client->request('GET', '/collections/1/collection-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('.list-heading:contains("Contributors") + .listing-list > .listing-list__item'));
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
        $this->assertSame('https://www.example.com/iiif/image/0,100,800,400/1800,900/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
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
