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
        $this->assertSame('Foo Bar', trim($crawler->filter('.content-header__author_list')->text()));
        $this->assertEmpty($crawler->filter('.content-header__institution_list'));
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
                [
                    'Accept' => [
                        'application/vnd.elife.article-poa+json; version=1',
                        'application/vnd.elife.article-vor+json; version=1',
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

        $client->request('GET', '/content/1/e00001');

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
                [
                    'Accept' => [
                        'application/vnd.elife.article-poa+json; version=1',
                        'application/vnd.elife.article-vor+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=1'],
                json_encode([
                    'status' => 'vor',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Author One et al',
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
                            'onBehalfOf' => [
                                'name' => ['Sub-Organisation One', 'Organisation One'],
                            ],
                        ],
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author Two',
                                'index' => 'Author Two',
                            ],
                            'onBehalfOf' => [
                                'name' => ['Sub-Organisation One', 'Organisation One'],
                            ],
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
                            'onBehalfOf' => [
                                'name' => ['Organisation Two'],
                            ],
                        ],
                    ],
                    'body' => [
                        [
                            'type' => 'section',
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

        $crawler = $client->request('GET', '/content/1/e00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertCount(6, $crawler->filter('.content-header__author_list_item'));
        $this->assertSame('Author One', trim($crawler->filter('.content-header__author_list_item')->eq(0)->text()));
        $this->assertSame('Author Two', trim($crawler->filter('.content-header__author_list_item')->eq(1)->text()));
        $this->assertSame('on behalf of Organisation One',
            trim($crawler->filter('.content-header__author_list_item')->eq(2)->text()));
        $this->assertSame('Author Three', trim($crawler->filter('.content-header__author_list_item')->eq(3)->text()));
        $this->assertSame('Author Four', trim($crawler->filter('.content-header__author_list_item')->eq(4)->text()));
        $this->assertSame('on behalf of Organisation Two',
            trim($crawler->filter('.content-header__author_list_item')->eq(5)->text()));
        $this->assertCount(3, $crawler->filter('.content-header__institution_list_item'));
        $this->assertSame('Institution One, Country One',
            trim($crawler->filter('.content-header__institution_list_item')->eq(0)->text()));
        $this->assertSame('Institution Two, Country Two',
            trim($crawler->filter('.content-header__institution_list_item')->eq(1)->text()));
        $this->assertSame('Institution Three',
            trim($crawler->filter('.content-header__institution_list_item')->eq(2)->text()));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                [
                    'Accept' => [
                        'application/vnd.elife.article-poa+json; version=1',
                        'application/vnd.elife.article-vor+json; version=1',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=1'],
                json_encode([
                    'status' => 'vor',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00+00:00',
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
                        ],
                    ],
                    'body' => [
                        [
                            'type' => 'section',
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

        return '/content/1/e00001';
    }
}
