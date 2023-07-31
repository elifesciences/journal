<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;
use test\eLife\Journal\Providers;

final class InsideElifeArticleControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_an_inside_elife_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Blog article title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertContains('Blog article text.', $crawler->filter('.wrapper--content')->text());
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
                'http://api.elifesciences.org/metrics/blog-article/1/page-views?by=month&page=1&per-page=20&order=desc',
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

        $breadcrumb = $crawler->filter('.breadcrumb-item a');
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals([
            [
                'Inside eLife',
                '/inside-elife',
            ],
        ], $breadcrumb->extract(['_text', 'href']));

        $this->assertSame('Blog article title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')
            ->text())));

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
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Blog article title | Inside eLife | eLife', $crawler->filter('title')->text());
        $this->assertSame('/inside-elife/1/blog-article-title', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/inside-elife/1/blog-article-title', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Blog article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Blog article impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Blog article impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('blog-article/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Blog article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
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

        $url = "/inside-elife/1{$slug}";

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
        $this->assertEquals(new TypedValue('Blog article title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/1',
                [
                    'Accept' => 'application/vnd.elife.blog-article+json; version=2',
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

        $client->request('GET', '/inside-elife/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/1',
                ['Accept' => 'application/vnd.elife.blog-article+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.blog-article+json; version=2'],
                json_encode([
                    'id' => '1',
                    'title' => 'Blog article <i>title</i>',
                    'published' => '2010-01-01T00:00:00Z',
                    'impactStatement' => 'Blog article impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Blog article text.',
                        ],
                    ],
                ])
            )
        );

        return '/inside-elife/1/blog-article-title';
    }
}
