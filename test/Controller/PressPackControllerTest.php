<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;

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
        $this->assertSame('Press pack Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertContains('Annotations', $crawler->filter('.contextual-data__list')->text());
        $this->assertContains('Press package text.', $crawler->filter('.wrapper--content')->text());
        $this->assertCount(0, $crawler->filter('.teaser--secondary'));
        $this->assertNotContains('Media contacts', $crawler->filter('.wrapper--content')->text());
        $this->assertNotContains('About', $crawler->filter('.wrapper--content')->text());
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
                'http://api.elifesciences.org/metrics/press-package/1/page-views?by=month&page=1&per-page=20&order=desc',
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
        $this->assertSame('Press package title', $crawler->filter('.content-header__title')->text());

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
    public function it_displays_media_contacts()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages/1',
                ['Accept' => 'application/vnd.elife.press-package+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=3'],
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
                    'mediaContacts' => [
                        [
                            'name' => [
                                'preferred' => 'Media Contact 1',
                                'index' => 'Media Contact 1',
                            ],
                            'emailAddresses' => [
                                'media-contact-1@example.com',
                            ],
                            'phoneNumbers' => [
                                '+12025550182;ext=555',
                            ],
                            'affiliations' => [
                                [
                                    'name' => [
                                        'Department of Molecular and Cell Biology',
                                        'University of California, Berkeley',
                                    ],
                                    'address' => [
                                        'formatted' => [
                                            'Berkeley',
                                            'United States',
                                        ],
                                        'components' => [
                                            'locality' => [
                                                'Berkeley',
                                            ],
                                            'country' => 'United States',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => [
                                'preferred' => 'Media Contact 2',
                                'index' => 'Media Contact 2',
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/for-the-press/1/press-package-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('.article-section:contains("Media contacts") .list > li'));
        $this->assertContains('Media Contact 1', $crawler->filter('.article-section:contains("Media contacts") .list > li:nth-child(1)')->text());
        $this->assertContains('Department of Molecular and Cell Biology, University of California, Berkeley', $crawler->filter('.article-section:contains("Media contacts") .list > li:nth-child(1)')->text());
        $this->assertContains('media-contact-1@example.com', $crawler->filter('.article-section:contains("Media contacts") .list > li:nth-child(1)')->text());
        $this->assertContains('+12025550182;ext=555', $crawler->filter('.article-section:contains("Media contacts") .list > li:nth-child(1)')->text());
        $this->assertContains('Media Contact 2', $crawler->filter('.article-section:contains("Media contacts") .list > li:nth-child(2)')->text());
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
                'http://api.elifesciences.org/press-packages/1',
                ['Accept' => 'application/vnd.elife.press-package+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=3'],
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

        $crawler = $client->request('GET', '/for-the-press/1/press-package-title');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Article title', $crawler->filter('.teaser--secondary ')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Press package title | For the press | eLife', $crawler->filter('title')->text());
        $this->assertSame('/for-the-press/1/press-package-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/for-the-press/1/press-package-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Press package title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertSame('press-package/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Press package title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
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

        $url = "/for-the-press/1{$slug}";

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
    public function it_displays_a_404_if_the_press_pack_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages/1',
                [
                    'Accept' => 'application/vnd.elife.press-package+json; version=3',
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
                ['Accept' => 'application/vnd.elife.press-package+json; version=3']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=3'],
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
                ])
            )
        );

        return '/for-the-press/1/press-package-title';
    }
}
