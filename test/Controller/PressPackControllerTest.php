<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;
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
        $breadcrumb = $crawler->filter('.breadcrumb-item a');
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals([
            [
                'Press Pack',
                '/for-the-press',
            ],
        ], $breadcrumb->extract(['_text', 'href']));

        $this->assertSame('Press package title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
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

        $this->assertSame('Open annotations (there are currently 0 annotations on this page).',
        $this->crawlerText($crawler->filter('.content-container-grid .side-section-wrapper__link')));

        $this->assertSame(
            [
                '5,678 views',
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
                ['Accept' => 'application/vnd.elife.press-package+json; version=4']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=4'],
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
                ['Accept' => 'application/vnd.elife.press-package+json; version=4']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=4'],
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
                            'type' => 'reviewed-preprint',
                            'id' => '00002',
                            'doi' => '10.1101/2023.03.08.531698',
                            'status' => 'reviewed',
                            'authorLine' => 'Author line',
                            'title' => 'Reviewed preprint title',
                            'published' => '2010-01-02T00:00:00Z',
                            'reviewedDate' => '2010-01-02T00:00:00Z',
                            'versionDate' => '2010-01-02T00:00:00Z',
                            'statusDate' => '2010-01-02T00:00:00Z',
                            'stage' => 'published',
                        ],
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
        $secondaryText = $crawler->filter('.grid-secondary-column__item')->text();
        $this->assertContains('Article title', $secondaryText);
        $this->assertContains('Reviewed preprint title', $secondaryText);
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
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
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

        $this->assertEquals('http://schema.org/Blog', $node->getType()->getId());
        $this->assertEquals(new TypedValue('Press package title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
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
                    'Accept' => 'application/vnd.elife.press-package+json; version=4',
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
                ['Accept' => 'application/vnd.elife.press-package+json; version=4']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.press-package+json; version=4'],
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
