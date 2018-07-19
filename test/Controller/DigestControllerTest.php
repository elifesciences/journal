<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;

/**
 * @backupGlobals enabled
 */
final class DigestControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @before
     */
    public function enableFeatureFlag()
    {
        $_ENV['FEATURE_DIGEST_CHANNEL'] = true;
    }

    /**
     * @test
     */
    public function it_does_not_display_a_digest_page_if_the_feature_flag_is_disabled()
    {
        $_ENV['FEATURE_DIGEST_CHANNEL'] = false;

        $client = static::createClient();

        $client->request('GET', $this->getUrl());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_an_digest_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Digest title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Digest Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')
            ->text())));
        $this->assertContains('Annotations', $crawler->filter('.contextual-data__list')->text());
        $this->assertContains('Digest text.', $crawler->filter('.wrapper--content')->text());
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
                'http://api.elifesciences.org/metrics/digest/1/page-views?by=month&page=1&per-page=20&order=desc',
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
        $this->assertSame('Digest title', $crawler->filter('.content-header__title')->text());

        $this->assertSame(
            [
                'Views 5,678',
                'Annotations Open annotations. The current annotation count on this page is being calculated.',
            ],
            array_map(function (string $text) {
                return trim(preg_replace('!\s+!', ' ', $text));
            }, $crawler->filter('.contextual-data__item')->extract('_text'))
        );
    }

    /**
     * @test
     */
    public function it_displays_collections()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Related to', $crawler->filter('.teaser--related .teaser__context_label')->text());
        $this->assertSame('Article 12345', trim(preg_replace('!\s+!', ' ', $crawler->filter('.teaser--related .teaser__header_text')
            ->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Digest title | eLife Science Digests | eLife', $crawler->filter('title')->text());
        $this->assertSame('/digests/1/digest-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/digests/1/digest-title', $crawler->filter('meta[property="og:url"]')
            ->attr('content'));
        $this->assertSame('Digest title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Digest impact statement', $crawler->filter('meta[property="og:description"]')
            ->attr('content'));
        $this->assertSame('Digest impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertSame('digest/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Digest title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 eLife Sciences Publications Limited. This article is distributed under the terms of the Creative Commons Attribution License, which permits unrestricted use and redistribution provided that the original author and source are credited.', $crawler->filter('meta[name="dc.rights"]')
            ->attr('content'));
    }

    /**
     * @test
     * @dataProvider incorrectSlugProvider
     */
    public function it_redirects_if_the_slug_is_not_correct(string $slug = null, string $queryString = null)
    {
        $client = static::createClient();

        $url = "/digests/1{$slug}";

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
    public function it_displays_a_404_if_the_digest_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests/1',
                [
                    'Accept' => 'application/vnd.elife.digest+json; version=1',
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

        $client->request('GET', '/digests/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests/1',
                ['Accept' => 'application/vnd.elife.digest+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.digest+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Digest <i>title</i>',
                    'impactStatement' => 'Digest impact statement',
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
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Digest text.',
                        ],
                    ],
                    'relatedContent' => [
                        [
                            'type' => 'research-article',
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '12345',
                            'version' => 1,
                            'doi' => '10.7554/eLife.12345',
                            'title' => 'Article 12345',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 5,
                            'elocationId' => 'e12345',
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        return '/digests/1/digest-title';
    }
}
