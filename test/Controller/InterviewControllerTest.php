<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;
use test\eLife\Journal\Providers;

final class InterviewControllerTest extends PageTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_displays_an_interview_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Interview title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Interview Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
        $this->assertContains('Annotations', $crawler->filter('.contextual-data__list')->text());
        $this->assertContains('Question?', $crawler->filter('.wrapper--content')->text());
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
                'http://api.elifesciences.org/metrics/interview/1/page-views?by=month&page=1&per-page=20&order=desc',
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
        $this->assertSame('Interview title', $crawler->filter('.content-header__title')->text());

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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections?page=1&per-page=10&order=desc&containing[]=interview/1',
                ['Accept' => 'application/vnd.elife.collection-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.collection-list+json; version=1'],
                json_encode([
                    'total' => 1,
                    'items' => [
                        [
                            'id' => '1',
                            'title' => 'Collection title',
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
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Part of Collection', $crawler->filter('.teaser--related .teaser__context_label_item')->text());
        $this->assertSame('Collection title', trim(preg_replace('!\s+!', ' ', $crawler->filter('.teaser--related .teaser__header_text')->text())));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Interview title | Interviews | eLife', $crawler->filter('title')->text());
        $this->assertSame('/interviews/1/interviewee', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/interviews/1/interviewee', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Interview title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Interview impact statement', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Interview impact statement', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('280', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('200', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('interview/1', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertSame('elifesciences.org', $crawler->filter('meta[name="dc.relation.ispartof"]')->attr('content'));
        $this->assertSame('Interview title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
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

        $url = "/interviews/1{$slug}";

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

        $this->assertEquals('http://schema.org/Conversation', $node->getType()->getId());
        $this->assertEquals(new TypedValue('Interview title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_interview_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/1',
                [
                    'Accept' => 'application/vnd.elife.interview+json; version=2',
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

        $client->request('GET', '/interviews/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_content()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Question?',
            $crawler->filter('main > div.wrapper > div > div > section:nth-of-type(1) > header > h3')->text());
        $this->assertSame('Answer.',
            $crawler->filter('main > div.wrapper > div > div > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('Interviewee CV',
            $crawler->filter('main > div.wrapper > div > div > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('2013 – Present: Somewhere',
            $crawler->filter('main > div.wrapper > div > div > section:nth-of-type(2) > div > ol > li')->text());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/1',
                ['Accept' => 'application/vnd.elife.interview+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.interview+json; version=2'],
                json_encode([
                    'id' => '1',
                    'interviewee' => [
                        'name' => [
                            'preferred' => 'Interviewee',
                            'index' => 'Interviewee',
                        ],
                        'cv' => [
                            [
                                'date' => '2013 – Present',
                                'text' => 'Somewhere',
                            ],
                        ],
                    ],
                    'title' => 'Interview title',
                    'published' => '2010-01-01T00:00:00Z',
                    'impactStatement' => 'Interview impact statement',
                    'content' => [
                        [
                            'type' => 'question',
                            'question' => 'Question?',
                            'answer' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Answer.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        return '/interviews/1/interviewee';
    }
}
