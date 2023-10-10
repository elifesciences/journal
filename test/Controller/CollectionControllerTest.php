<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;
use test\eLife\Journal\Providers;

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
    public function it_displays_metrics()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/collection/1/page-views?by=month&page=1&per-page=20&order=desc',
                ['Accept' => 'application/vnd.elife.metric-time-period+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.metric-time-period+json; version=1'],
                json_encode([
                    'totalPeriods' => 2,
                    'totalValue' => 5678,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => 2839,
                        ],
                        [
                            'period' => '2016-01-02',
                            'value' => 2839,
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Collection title', $crawler->filter('.content-header__title')->text());

        $this->assertSame(
            [
                'Views 5,678',
            ],
            array_map(function (string $text) {
                return trim(preg_replace('!\s+!', ' ', $text));
            }, $crawler->filter('.contextual-data__item')->extract('_text'))
        );
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
                ['Accept' => 'application/vnd.elife.collection+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person',
                            'index' => 'Person',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'senior-editor',
                                'label' => 'Senior editor',
                            ],
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
                ['Accept' => 'application/vnd.elife.collection+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person',
                            'index' => 'Person',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'senior-editor',
                                'label' => 'Senior editor',
                            ],
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
                ['Accept' => 'application/vnd.elife.collection+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 1800,
                                'height' => 1600,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person One',
                            'index' => 'Person One',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'reviewing-editor',
                                'label' => 'Reviewing editor',
                            ],
                            'name' => [
                                'preferred' => 'Person Two',
                                'index' => 'Person Two',
                            ],
                        ],
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'senior-editor',
                                'label' => 'Senior editor',
                            ],
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
    public function it_displays_reviewed_preprints()
    {
        $client = static::createClient();
        $url = '/collections/1/collection-title';

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections/1',
                ['Accept' => 'application/vnd.elife.collection+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 3872,
                                'height' => 2581,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person',
                            'index' => 'Person',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'senior-editor',
                                'label' => 'Senior editor',
                            ],
                            'name' => [
                                'preferred' => 'Person',
                                'index' => 'Person',
                            ],
                        ],
                    ],
                    'content' => [
                        [
                            "type" => "reviewed-preprint",
                            "id" => "19560",
                            "doi" => "10.7554/eLife.19560",
                            "status" => "reviewed",
                            "authorLine" => "Lee R Berger, John Hawks ... Scott A Williams",
                            "title" => "Collection with reviewed-preprint",
                            "indexContent" => "<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa",
                            "titlePrefix" => "Title prefix",
                            "stage" => "published",
                            "published" => "2022-08-01T00:00:00Z",
                            "reviewedDate" => "2022-08-01T00:00:00Z",
                            "versionDate" => "2022-08-05T00:00:00Z",
                            "statusDate" => "2022-08-01T00:00:00Z",
                            "volume" => 4,
                            "elocationId" => "e19560",
                        ],
                    ],
                ])
            )
        );


        $crawler = $client->request('GET', $url);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Collection title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Collection with reviewed-preprint', trim($crawler->filter('#maincontent #primaryListing .teaser__header_text')->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Collection title | Collections | eLife', $crawler->filter('title')->text());
        $this->assertSame('/collections/1/collection-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/collections/1/collection-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Collection title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Collection impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Collection impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/ban%2Fner/full/2000,/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('2000', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('1333', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('collection/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Collection title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 eLife Sciences Publications Limited. This article is distributed under the terms of the Creative Commons Attribution License, which permits unrestricted use and redistribution provided that the original author and source are credited.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
    }

    /**
     * @test
     * @dataProvider incorrectSlugProvider
     */
    public function it_redirects_if_the_slug_is_not_correct(string $slug = null, string $queryString = null)
    {
        $client = static::createClient();

        $url = "/collections/1{$slug}";

        $expectedUrl = $this->getUrl();
        if ($queryString) {
            $url .= "?{$queryString}";
            $expectedUrl .= "?{$queryString}";
        }

        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect($expectedUrl));
    }

    /**
     * @test
     */
    public function it_has_schema_org_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $script = $crawler->filter('script[type="application/ld+json"]');
        $this->assertNotEmpty($script);

        $value = $script->text();
        $this->assertJson($value);

        $this->markTestIncomplete('This test fails if schema.org is broken!');

        $graph = JsonLD::getDocument($value)->getGraph();
        $node = $graph->getNodes()[0];

        $this->assertEquals('http://schema.org/Collection', $node->getType()->getId());
        $this->assertEquals(new TypedValue('Collection title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
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
                    'Accept' => 'application/vnd.elife.collection+json; version=3',
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
                ['Accept' => 'application/vnd.elife.collection+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection+json; version=3'],
                json_encode([
                    'id' => '1',
                    'title' => 'Collection title',
                    'published' => '2010-01-01T00:00:00Z',
                    'updated' => '2011-01-01T00:00:00Z',
                    'image' => [
                        'banner' => [
                            'uri' => 'https://www.example.com/iiif/ban%2Fner',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/banner.jpg',
                                'filename' => 'image.jpg',
                            ],
                            'size' => [
                                'width' => 3872,
                                'height' => 2581,
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
                        'type' => [
                            'id' => 'senior-editor',
                            'label' => 'Senior editor',
                        ],
                        'name' => [
                            'preferred' => 'Person',
                            'index' => 'Person',
                        ],
                    ],
                    'curators' => [
                        [
                            'id' => 'person',
                            'type' => [
                                'id' => 'senior-editor',
                                'label' => 'Senior editor',
                            ],
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
