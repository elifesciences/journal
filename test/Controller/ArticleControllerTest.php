<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ArticleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_article_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertEmpty($crawler->filter('.content-header__institution_list'));
        $this->assertSame('Research Article Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));

        $this->assertNotContains('Annotations', $crawler->filter('.contextual-data__list')->text());
        $this->assertContains('Cite as: eLife 2010;1:e00001',
            $crawler->filter('.contextual-data__cite_wrapper')->text());
        $this->assertContains('doi: 10.7554/eLife.00001', $crawler->filter('.contextual-data__cite_wrapper')->text());
    }

    /**
     * @test
     */
    public function it_displays_an_preview_article_page()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=2'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'preview',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'vor',
                            'stage' => 'preview',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertEmpty($crawler->filter('.content-header__institution_list'));

        $this->assertCount(0, $crawler->filter('.contextual-data__cite_wrapper'));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Article title | eLife', $crawler->filter('title')->text());
        $this->assertSame('/articles/00001', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/articles/00001', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertSame('doi:10.7554/eLife.00001', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertSame('Article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 Bar. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
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
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
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

        $client->request('GET', '/articles/00001');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_the_author_and_institution_lists()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=2'],
                json_encode([
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One et al.',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author One',
                                'index' => 'Author One',
                            ],
                            'affiliations' => [
                                [
                                    'name' => ['Department One', 'Institution One'],
                                    'address' => [
                                        'formatted' => ['Locality One', 'Country One'],
                                        'components' => [
                                            'locality' => ['Locality One'],
                                            'country' => 'Country One',
                                        ],
                                    ],
                                ],
                                [
                                    'name' => ['Department Two', 'Institution Two'],
                                    'address' => [
                                        'formatted' => ['Locality Two', 'Country Two'],
                                        'components' => [
                                            'locality' => ['Locality Two'],
                                            'country' => 'Country Two',
                                        ],
                                    ],
                                ],
                            ],
                            'equalContributionGroups' => [1],
                        ],
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author Two',
                                'index' => 'Author Two',
                            ],
                            'equalContributionGroups' => [1],
                        ],
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author Three',
                                'index' => 'Author Three',
                            ],
                            'affiliations' => [
                                [
                                    'name' => ['Department One', 'Institution One'],
                                    'address' => [
                                        'formatted' => ['Locality One', 'Country One'],
                                        'components' => [
                                            'locality' => ['Locality One'],
                                            'country' => 'Country One',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author Four',
                                'index' => 'Author Four',
                            ],
                            'affiliations' => [
                                [
                                    'name' => ['Institution Three'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'on-behalf-of',
                            'onBehalfOf' => 'on behalf of Institution Four',
                        ],
                    ],
                    'abstract' => [
                        'doi' => '10.7554/eLife.09560.001',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Abstract text',
                            ],
                        ],
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertCount(5, $crawler->filter('.content-header__author_list_item'));
        $this->assertSame('Author One', trim($crawler->filter('.content-header__author_list_item')->eq(0)->text()));
        $this->assertSame('Author Two', trim($crawler->filter('.content-header__author_list_item')->eq(1)->text()));
        $this->assertSame('Author Three', trim($crawler->filter('.content-header__author_list_item')->eq(2)->text()));
        $this->assertSame('Author Four', trim($crawler->filter('.content-header__author_list_item')->eq(3)->text()));
        $this->assertSame('on behalf of Institution Four',
            trim($crawler->filter('.content-header__author_list_item')->eq(4)->text()));
        $this->assertCount(3, $crawler->filter('.content-header__institution_list_item'));
        $this->assertSame('Institution One, Country One',
            trim($crawler->filter('.content-header__institution_list_item')->eq(0)->text()));
        $this->assertSame('Institution Two, Country Two',
            trim($crawler->filter('.content-header__institution_list_item')->eq(1)->text()));
        $this->assertSame('Institution Three',
            trim($crawler->filter('.content-header__institution_list_item')->eq(2)->text()));
    }

    /**
     * @test
     */
    public function it_may_not_have_authors()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=2'],
                json_encode([
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertCount(0, $crawler->filter('.content-header__author_list_item'));
        $this->assertCount(0, $crawler->filter('.content-header__institution_list_item'));

        $articleInfo = $crawler->filter('.grid-column > section:nth-of-type(2)');
        $this->assertSame('Article information', $articleInfo->filter('header > h2')->text());
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
                'http://api.elifesciences.org/metrics/article/00001/citations',
                ['Accept' => 'application/vnd.elife.metric-citations+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.metric-citations+json; version=1'],
                json_encode([
                    [
                        'service' => 'Service One',
                        'uri' => 'http://www.example.com/',
                        'citations' => 123,
                    ],
                    [
                        'service' => 'Service Two',
                        'uri' => 'http://www.example.com/',
                        'citations' => 1234,
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/article/00001/page-views?by=month&page=1&per-page=20&order=desc',
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

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/article/00001/downloads?by=month&page=1&per-page=20&order=desc',
                ['Accept' => 'application/vnd.elife.metric-time-period+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.metric-time-period+json; version=1'],
                json_encode([
                    'totalPeriods' => 2,
                    'totalValue' => 9012,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => 4506,
                        ],
                        [
                            'period' => '2016-01-02',
                            'value' => 4506,
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());

        $this->assertSame(
            [
                'Cited 1,234',
                'Views 5,678',
                'Comments 0',
            ],
            array_map('trim', $crawler->filter('.contextual-data__item')->extract('_text'))
        );

        $metrics = $crawler->filter('.grid-column > section:nth-of-type(3)');
        $this->assertSame('Metrics', $metrics->filter('header > h2')->text());
        $this->assertContains('5,678', $metrics->filter('.statistic:contains("Page views") .statistic__value')->text());
        $this->assertCount(1, $metrics->filter('[data-behaviour="Metrics"][data-type="article"][data-id="00001"][data-metric="page-views"]'));
        $this->assertContains('9,012', $metrics->filter('.statistic:contains("Downloads") .statistic__value')->text());
        $this->assertCount(1, $metrics->filter('[data-behaviour="Metrics"][data-type="article"][data-id="00001"][data-metric="downloads"]'));
        $this->assertContains('1,234', $metrics->filter('.statistic:contains("Citations") .statistic__value')->text());
        $this->assertContains('Article citation count generated by polling the highest count across the following sources: Service One, Service Two.', $metrics->text());
    }

    /**
     * @test
     */
    public function it_displays_a_poa()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=2'],
                json_encode([
                    'status' => 'poa',
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One et al.',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author One',
                                'index' => 'Author One',
                            ],
                        ],
                    ],
                    'reviewers' => [
                        [
                            'name' => [
                                'preferred' => 'Reviewer 1',
                                'index' => 'Reviewer 1',
                            ],
                            'role' => 'role',
                            'affiliations' => [
                                [
                                    'name' => ['Institution'],
                                    'address' => [
                                        'formatted' => ['Country'],
                                        'components' => [
                                            'country' => 'Country',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => [
                                'preferred' => 'Reviewer 2',
                                'index' => 'Reviewer 2',
                            ],
                            'role' => 'role',
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'poa',
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));

        $this->assertNull($crawler->filter('.view-selector')->attr('data-side-by-side-link'));
        $articleInfo = $crawler->filter('.grid-column > section:nth-of-type(1)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $authorDetails = $articleInfo->filter('div > ol:nth-of-type(1) > li.authors-details__author');
        $this->assertCount(1, $authorDetails);
        $this->assertSame('Author One', $authorDetails->eq(0)->filter('.author-details__name')->text());

        $articleInfo = $crawler->filter('.grid-column > section:nth-of-type(1) > div > section');

        $reviewers = $articleInfo->eq(0);
        $this->assertSame('Reviewing Editor', $reviewers->filter('header > h3')->text());

        $publicationHistory = $articleInfo->eq(1);
        $this->assertSame('Publication history', $publicationHistory->filter('header > h3')->text());
        $this->assertCount(1, $publicationHistory->filter('ol')->children());
        $this->assertSame('Accepted Manuscript published: January 1, 2010 (version 1)', $publicationHistory->filter('ol')->children()->eq(0)->text());

        $copyright = $articleInfo->eq(2);
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Author One', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

        $this->assertSame('Comments', $crawler->filter('.grid-column > section:nth-of-type(2) .article-section__header_text')->text());

        $this->assertSame('Download links', $crawler->filter('.grid-column > section:nth-of-type(3) .article-section__header_text')->text());

        $this->assertSame('Categories and tags', $crawler->filter('.grid-column > section:nth-of-type(4) .article-meta__group_title')->text());
    }

    /**
     * @test
     */
    public function it_displays_previous_versions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Read the most recent version of this article.', array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));
    }

    /**
     * @test
     */
    public function it_has_metadata_on_previous_versions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Article title | eLife', $crawler->filter('title')->text());
        $this->assertSame('/articles/00001', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/articles/00001', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('article', $crawler->filter('meta[property="og:type"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertSame('doi:10.7554/eLife.00001', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertSame('Article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 Author One. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_displays_content()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=2'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 4,
                    'type' => 'research-advance',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'titlePrefix' => 'Title prefix',
                    'published' => '2007-01-01T00:00:00Z',
                    'versionDate' => '2010-01-01T00:00:00Z',
                    'statusDate' => '2009-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Foo Bar',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Foo Bar',
                                'index' => 'Bar, Foo',
                            ],
                            'role' => 'Role',
                        ],
                        [
                            'type' => 'group',
                            'name' => 'Baz',
                        ],
                    ],
                    'reviewers' => [
                        [
                            'name' => [
                                'preferred' => 'Reviewer 1',
                                'index' => 'Reviewer 1',
                            ],
                            'role' => 'role',
                            'affiliations' => [
                                [
                                    'name' => ['Institution'],
                                    'address' => [
                                        'formatted' => ['Country'],
                                        'components' => [
                                            'country' => 'Country',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => [
                                'preferred' => 'Reviewer 2',
                                'index' => 'Reviewer 2',
                            ],
                            'role' => 'role',
                        ],
                    ],
                    'abstract' => [
                        'doi' => '10.7554/eLife.09560.001',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Abstract text',
                            ],
                        ],
                    ],
                    'digest' => [
                        'doi' => '10.7554/eLife.09560.002',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Digest text',
                            ],
                        ],
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Body title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Body text',
                                ],
                                [
                                    'type' => 'figure',
                                    'assets' => [
                                        [
                                            'type' => 'image',
                                            'id' => 'image1',
                                            'label' => 'Image 1 label',
                                            'title' => 'Image 1 title',
                                            'image' => [
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
                                        [
                                            'type' => 'image',
                                            'id' => 'image1s1',
                                            'label' => 'Image 1 supplement 1 label',
                                            'title' => 'Image 1 supplement 1 title',
                                            'image' => [
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
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'appendices' => [
                        [
                            'id' => 'app1',
                            'doi' => '10.7554/eLife.09560.005',
                            'title' => 'Appendix 1',
                            'content' => [
                                [
                                    'type' => 'section',
                                    'id' => 'app1-1',
                                    'title' => 'Appendix title',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Appendix text',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'references' => [
                        [
                            'type' => 'journal',
                            'id' => 'bib1',
                            'date' => '2013',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'Person One',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'articleTitle' => 'Journal article',
                            'journal' => 'A journal',
                            'pages' => 'In press',
                        ],
                    ],
                    'acknowledgements' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Acknowledgements text',
                        ],
                    ],
                    'ethics' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Ethics text',
                        ],
                    ],
                    'funding' => [
                        'awards' => [
                            [
                                'id' => 'award1',
                                'source' => [
                                    'name' => [
                                        'Funding source',
                                    ],
                                ],
                                'awardId' => 'Award ID',
                                'recipients' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Foo Bar',
                                            'index' => 'Bar, Foo',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'id' => 'award2',
                                'source' => [
                                    'name' => [
                                        'Other funding source',
                                    ],
                                ],
                                'recipients' => [
                                    [
                                        'type' => 'group',
                                        'name' => 'Baz',
                                    ],
                                ],
                            ],
                        ],
                        'statement' => 'Funding statement',
                    ],
                    'decisionLetter' => [
                        'doi' => '10.7554/eLife.09560.003',
                        'description' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Decision letter description',
                            ],
                        ],
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Decision letter text',
                            ],
                        ],
                    ],
                    'authorResponse' => [
                        'doi' => '10.7554/eLife.09560.003',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Author response text',
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'received' => '2006-12-30',
                    'accepted' => '2006-12-31',
                    'versions' => [
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-advance',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'titlePrefix' => 'Title prefix',
                            'published' => '2007-01-01T00:00:00Z',
                            'versionDate' => '2007-01-01T00:00:00Z',
                            'statusDate' => '2007-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 2,
                            'type' => 'research-advance',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'titlePrefix' => 'Title prefix',
                            'published' => '2007-01-01T00:00:00Z',
                            'versionDate' => '2008-01-01T00:00:00Z',
                            'statusDate' => '2007-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 3,
                            'type' => 'research-advance',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'titlePrefix' => 'Title prefix',
                            'published' => '2007-01-01T00:00:00Z',
                            'versionDate' => '2009-01-01T00:00:00Z',
                            'statusDate' => '2009-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 4,
                            'type' => 'research-advance',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'titlePrefix' => 'Title prefix',
                            'published' => '2008-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2009-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame('Title prefix: Article title', $crawler->filter('meta[name="citation_title"]')->attr('content'));
        $this->assertSame('10.7554/eLife.00001', $crawler->filter('meta[name="citation_doi"]')->attr('content'));
        $this->assertSame('eLife 2007;1:e00001', $crawler->filter('meta[name="citation_id"]')->attr('content'));
        $this->assertSame('2007/01/01', $crawler->filter('meta[name="citation_publication_date"]')->attr('content'));
        $this->assertCount(2, $crawler->filter('meta[name="citation_author"]'));
        $this->assertCount(1, $crawler->filter('meta[name="citation_reference"]'));

        $this->assertSame('Title prefix: Article title', $crawler->filter('.content-header__title')->text());

        $this->assertSame('Abstract',
            $crawler->filter('.grid-column > section:nth-of-type(1) > header > h2')->text());
        $this->assertSame('Abstract text',
            $crawler->filter('.grid-column > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.001',
            $crawler->filter('.grid-column > section:nth-of-type(1) > div > .doi')->text());
        $this->assertSame('eLife digest',
            $crawler->filter('.grid-column > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('Digest text',
            $crawler->filter('.grid-column > section:nth-of-type(2) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.002',
            $crawler->filter('.grid-column > section:nth-of-type(2) > div > .doi')->text());
        $this->assertSame('Body title',
            $crawler->filter('.grid-column > section:nth-of-type(3) > header > h2')->text());

        $body = $crawler->filter('.grid-column > section:nth-of-type(3) > div')->children();
        $this->assertCount(2, $body);

        $this->assertSame('Body text', $body->eq(0)->text());
        $this->assertSame('Image 1 label with 1 supplement see all', trim($body->eq(1)->filter('.asset-viewer-inline__header_text')->text()));

        $appendix = $crawler->filter('.grid-column > section:nth-of-type(4)');
        $this->assertSame('Appendix 1', $appendix->filter('header > h2')->text());
        $this->assertSame('Appendix title', $appendix->filter('div > section > header > h3')->text());
        $this->assertSame('Appendix text', $appendix->filter('div > p')->text());
        $references = $crawler->filter('.grid-column > section:nth-of-type(5)');
        $this->assertSame('References',
            $references->filter('header > h2')->text());
        $this->assertSame('1',
            $references->filter('div > ol > li:nth-of-type(1) .reference-list__ordinal_number')->text());
        $this->assertSame('Journal article',
            $references->filter('div > ol > li:nth-of-type(1) .reference__title')->text());
        $this->assertSame('Decision letter',
            $crawler->filter('.grid-column > section:nth-of-type(6) > header > h2')->text());
        $this->assertCount(2, $crawler->filter('.grid-column > section:nth-of-type(6) > div .profile-snippet__name'));
        $this->assertSame('Reviewer 1',
            $crawler->filter('.grid-column > section:nth-of-type(6) > div .profile-snippet__name')->eq(0)->text());
        $this->assertSame('Reviewer 2',
            $crawler->filter('.grid-column > section:nth-of-type(6) > div .profile-snippet__name')->eq(1)->text());
        $this->assertSame('Decision letter description',
            $crawler->filter('.grid-column > section:nth-of-type(6) > div .decision-letter-header__main_text > p')->text());
        $this->assertSame('Decision letter text',
            $crawler->filter('.grid-column > section:nth-of-type(6) > div > p')->text());
        $this->assertSame('Author response',
            $crawler->filter('.grid-column > section:nth-of-type(7) > header > h2')->text());
        $this->assertSame('Author response text',
            $crawler->filter('.grid-column > section:nth-of-type(7) > div > p')->text());

        $articleInfo = $crawler->filter('.grid-column > section:nth-of-type(8)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $authorDetails = $articleInfo->filter('div > ol:nth-of-type(1) > li.authors-details__author');
        $this->assertCount(2, $authorDetails);
        $this->assertSame('Foo Bar, Role', $authorDetails->eq(0)->filter('.author-details__name')->text());
        $this->assertSame('Baz', $authorDetails->eq(1)->filter('.author-details__name')->text());

        $articleInfo = $crawler->filter('.grid-column > section:nth-of-type(8) > div > section');

        $funding = $articleInfo->eq(0);
        $this->assertSame('Funding', $funding->filter('header > h3')->text());
        $this->assertSame('Funding source (Award ID)', $funding->filter('.article-section__body .article-section__header_text')->eq(0)->text());
        $this->assertSame('Foo Bar', trim($funding->filter('.article-section__body .article-section__body')->eq(0)->text()));
        $this->assertSame('Other funding source', $funding->filter('.article-section__body .article-section__header_text')->eq(1)->text());
        $this->assertSame('Baz', trim($funding->filter('.article-section__body .article-section__body')->eq(1)->text()));
        $this->assertSame('Funding statement', $funding->filter('p')->text());

        $acknowledgements = $articleInfo->eq(1);
        $this->assertSame('Acknowledgements', $acknowledgements->filter('header > h3')->text());
        $this->assertSame('Acknowledgements text', trim($acknowledgements->filter('div')->text()));

        $ethics = $articleInfo->eq(2);
        $this->assertSame('Ethics', $ethics->filter('header > h3')->text());
        $this->assertSame('Ethics text', trim($ethics->filter('div')->text()));

        $reviewers = $articleInfo->eq(3);
        $this->assertSame('Reviewing Editor', $reviewers->filter('header > h3')->text());

        $reviewerDetails = $reviewers->filter('li');
        $this->assertCount(2, $reviewerDetails);
        $this->assertSame('Reviewer 1, role, Institution, Country', $reviewerDetails->eq(0)->text());
        $this->assertSame('Reviewer 2, role', $reviewerDetails->eq(1)->text());

        $publicationHistory = $articleInfo->eq(4);
        $this->assertSame('Publication history', $publicationHistory->filter('header > h3')->text());
        $this->assertCount(6, $publicationHistory->filter('ol')->children());
        $this->assertSame('Received: December 30, 2006', $publicationHistory->filter('ol')->children()->eq(0)->text());
        $this->assertSame('Accepted: December 31, 2006', $publicationHistory->filter('ol')->children()->eq(1)->text());
        $this->assertSame('Accepted Manuscript published: January 1, 2007 (version 1)', $publicationHistory->filter('ol')->children()->eq(2)->text());
        $this->assertSame('Accepted Manuscript updated: January 1, 2008 (version 2)', $publicationHistory->filter('ol')->children()->eq(3)->text());
        $this->assertSame('Version of Record published: January 1, 2009 (version 3)', $publicationHistory->filter('ol')->children()->eq(4)->text());
        $this->assertSame('Version of Record updated: January 1, 2010 (version 4)', $publicationHistory->filter('ol')->children()->eq(5)->text());

        $copyright = $articleInfo->eq(5);
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Bar', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

        $this->assertSame('Comments', $crawler->filter('.grid-column > section:nth-of-type(9) .article-section__header_text')->text());

        $this->assertSame('Download links', $crawler->filter('.grid-column > section:nth-of-type(10) .article-section__header_text')->text());

        $this->assertSame('Categories and tags', $crawler->filter('.grid-column > section:nth-of-type(11) .article-meta__group_title')->text());

        $this->assertRegexp('|^https://.*/00001$|', $crawler->filter('.view-selector')->attr('data-side-by-side-link'));

        $this->assertSame(
            [
                'Abstract',
                'eLife digest',
                'Body title',
                'Appendix 1',
                'References',
                'Decision letter',
                'Author response',
                'Article and author information',
                'Comments',
            ],
            array_map('trim', $crawler->filter('.view-selector__jump_link_item')->extract('_text'))
        );
    }

    /**
     * @test
     */
    public function it_shows_annotations_rather_than_comments_when_the_feature_flag_is_enabled()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "{$this->getUrl()}?open-sesame");

        $this->assertNotContains('Comments', $crawler->text());
        $this->assertContains('Annotations', $crawler->filter('.contextual-data__list')->text());
    }

    /**
     * @test
     */
    public function it_displays_recommendations()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=2'],
                json_encode([
                    'status' => 'poa',
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One et al.',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author One',
                                'index' => 'Author One',
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'poa',
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/related',
                [
                    'Accept' => [
                        'application/vnd.elife.article-related+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-related+json; version=1'],
                json_encode([
                    [
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '00002',
                        'version' => 1,
                        'type' => 'correction',
                        'doi' => '10.7554/eLife.00002',
                        'title' => 'Correction title',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 1,
                        'elocationId' => 'e00002',
                        'copyright' => [
                            'license' => 'CC-BY-4.0',
                            'holder' => 'Author One',
                            'statement' => 'Copyright statement.',
                        ],
                        'authorLine' => 'Author One et al.',
                    ],
                    [
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '00003',
                        'version' => 1,
                        'type' => 'retraction',
                        'doi' => '10.7554/eLife.00003',
                        'title' => 'Retraction title',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 1,
                        'elocationId' => 'e00003',
                        'copyright' => [
                            'license' => 'CC-BY-4.0',
                            'holder' => 'Author One',
                            'statement' => 'Copyright statement.',
                        ],
                        'authorLine' => 'Author One et al.',
                    ],
                    [
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '00004',
                        'version' => 1,
                        'type' => 'insight',
                        'doi' => '10.7554/eLife.00004',
                        'title' => 'Insight 1 title',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 1,
                        'elocationId' => 'e00004',
                        'copyright' => [
                            'license' => 'CC-BY-4.0',
                            'holder' => 'Author One',
                            'statement' => 'Copyright statement.',
                        ],
                        'authorLine' => 'Author One et al.',
                    ],
                    [
                        'status' => 'vor',
                        'stage' => 'published',
                        'id' => '00005',
                        'version' => 1,
                        'type' => 'insight',
                        'doi' => '10.7554/eLife.00005',
                        'title' => 'Insight 2 title',
                        'published' => '2010-01-01T00:00:00Z',
                        'versionDate' => '2010-01-01T00:00:00Z',
                        'statusDate' => '2010-01-01T00:00:00Z',
                        'volume' => 1,
                        'elocationId' => 'e00005',
                        'copyright' => [
                            'license' => 'CC-BY-4.0',
                            'holder' => 'Author One',
                            'statement' => 'Copyright statement.',
                        ],
                        'authorLine' => 'Author One et al.',
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/recommendations/article/00001?page=1&per-page=100&order=desc',
                [
                    'Accept' => [
                        'application/vnd.elife.recommendations+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.recommendations+json; version=1'],
                json_encode([
                    'total' => 7,
                    'items' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00002',
                            'version' => 1,
                            'type' => 'correction',
                            'doi' => '10.7554/eLife.00002',
                            'title' => 'Correction title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00002',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00003',
                            'version' => 1,
                            'type' => 'retraction',
                            'doi' => '10.7554/eLife.00003',
                            'title' => 'Retraction title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00003',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00004',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00004',
                            'title' => 'Insight 1 title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00004',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00005',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00005',
                            'title' => 'Insight 2 title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00005',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00006',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00006',
                            'title' => 'Another article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00006',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00006',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00006',
                            'title' => 'Insight 3 title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00006',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00007',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00007',
                            'title' => 'Insight 4 title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00007',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('This article has been corrected. Read the correction notice.', trim($crawler->filter('.info-bar')->eq(1)->text()));
        $this->assertSame('This article has been retracted. Read the retraction notice.', trim($crawler->filter('.info-bar')->eq(2)->text()));
        $this->assertContains('Insight 1 title', $crawler->filter('.teaser--related')->text());

        $furtherReading = $crawler->filter('.listing-list-heading:contains("Further reading") + .listing-list > .listing-list__item');
        $this->assertCount(3, $furtherReading);
        $this->assertCount(1, $crawler->filter('.listing-list__item--related'));
        $this->assertContains('Insight 1 title', $furtherReading->eq(0)->text());
        $this->assertContains('Insight 2 title', $furtherReading->eq(1)->text());
        $this->assertContains('Another article title', $furtherReading->eq(2)->text());

        $crawler = $client->click($crawler->selectLink('Load more')->link());

        $furtherReading = $crawler->filter('.listing-list__item');
        $this->assertCount(2, $furtherReading);
        $this->assertCount(0, $crawler->filter('.listing-list__item--related'));
        $this->assertContains('Insight 3 title', $furtherReading->eq(0)->text());
        $this->assertContains('Insight 4 title', $furtherReading->eq(1)->text());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=2'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 3,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00Z',
                    'versionDate' => '2012-01-01T00:00:00Z',
                    'statusDate' => '2011-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Fossil hominins were first recognized in the Dinaledi Chamber in the Rising Star cave system in October 2013. During a relatively short excavation, our team recovered an extensive collection of 1550 hominin specimens, representing nearly every element of the skeleton multiple times (Figure 1), including many complete elements and morphologically informative fragments, some in articulation, as well as smaller fragments many of which could be refit into more complete elements. The collection is a morphologically homogeneous sample that can be attributed to no previously-known hominin species. Here we describe this new species, <i>Homo naledi</i>. We have not defined <i>H. naledi</i> narrowly based on a single jaw or skull because the entire body of material has informed our understanding of its biology.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        return '/articles/00001';
    }

    private function getPreviousVersionUrl() : string
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions/1',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=2, application/vnd.elife.article-vor+json; version=2']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=2'],
                json_encode([
                    'status' => 'poa',
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One et al.',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author One',
                                'index' => 'Author One',
                            ],
                        ],
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions',
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=1'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'poa',
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
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 2,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2011-01-01T00:00:00Z',
                            'statusDate' => '2011-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => 'e00001',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Author One et al.',
                        ],
                    ],
                ])
            )
        );

        return '/articles/00001v1';
    }
}
