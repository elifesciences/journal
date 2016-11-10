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

        $this->assertContains('Cite as: eLife 2012;1:e00001',
            $crawler->filter('.contextual-data__cite_wrapper')->text());
        $this->assertContains('doi: 10.7554/eLife.00001', $crawler->filter('.contextual-data__cite_wrapper')->text());
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
                    'statusDate' => '2010-01-01T00:00:00+00:00',
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
                        ],
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author Two',
                                'index' => 'Author Two',
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
                        ],
                        [
                            'type' => 'on-behalf-of',
                            'onBehalfOf' => 'on behalf of Institution Four',
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

        $crawler = $client->request('GET', '/content/1/e00001');

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
    public function it_displays_a_poa()
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
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=1'],
                json_encode([
                    'status' => 'poa',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'statusDate' => '2010-01-01T00:00:00+00:00',
                    'volume' => 1,
                    'elocationId' => 'e00001',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
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
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/content/1/e00001');
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));
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
                    'type' => 'research-advance',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'titlePrefix' => 'Title prefix',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'statusDate' => '2010-01-01T00:00:00+00:00',
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
                            'journal' => [
                                'name' => [
                                    'A journal',
                                ],
                            ],
                            'pages' => 'In press',
                        ],
                    ],
                    'acknowledgements' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Acknowledgements text',
                        ],
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

        $crawler = $client->request('GET', '/content/1/e00001');

        $this->assertSame('Title prefix: Article title', $crawler->filter('.content-header__title')->text());

        $this->assertSame('Abstract',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > header > h2')->text());
        $this->assertSame('Abstract text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('eLife digest',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('Digest text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(2) > div > p')->text());
        $this->assertSame('Body title',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(3) > header > h2')->text());
        $this->assertSame('Body text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(3) > div > p')->text());
        $appendix = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(4)');
        $this->assertSame('Appendix 1', $appendix->filter('header > h2')->text());
        $this->assertSame('Appendix title', $appendix->filter('div > section > header > h3')->text());
        $this->assertSame('Appendix text', $appendix->filter('div > p')->text());
        $references = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(5)');
        $this->assertSame('References',
            $references->filter('header > h2')->text());
        $this->assertSame('1',
            $references->filter('div > ol > li:nth-of-type(1) .reference-list__ordinal_number')->text());
        $this->assertSame('Journal article',
            $references->filter('div > ol > li:nth-of-type(1) .reference__title')->text());
        $this->assertSame('Decision letter',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(6) > header > h2')->text());
        $this->assertSame('Decision letter text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(6) > div > p')->text());
        $this->assertSame('Author response',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(7) > header > h2')->text());
        $this->assertSame('Author response text',
            $crawler->filter('main > .wrapper > div > div > section:nth-of-type(7) > div > p')->text());

        $articleInfo = $crawler->filter('main > .wrapper > div > div > section:nth-of-type(8)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $acknowledgements = $articleInfo->filter('div > section:nth-of-type(1)');
        $this->assertSame('Acknowledgements', $acknowledgements->filter('header > h3')->text());
        $this->assertSame('Acknowledgements text', trim($acknowledgements->filter('div')->text()));

        $copyright = $articleInfo->filter('div > section:nth-of-type(2)');
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Bar', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

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
            ],
            array_map('trim', $crawler->filter('.view-selector__jump_link_item')->extract('_text'))
        );
    }

    /**
     * @test
     */
    public function it_displays_content_without_sections_if_there_are_not_any()
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
                    'type' => 'research-exchange',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'statusDate' => '2010-01-01T00:00:00+00:00',
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
                            'id' => 's-1',
                            'title' => 'Body title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Body text',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/content/1/e00001');

        $this->assertNotContains('Body title', $crawler->text());
        $this->assertSame('Body text', $crawler->filter('main > .wrapper > div > div > p')->text());
        $this->assertEmpty($crawler->filter('.view-selector'));
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
                    'statusDate' => '2010-01-01T00:00:00+00:00',
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

        return '/content/1/e00001';
    }
}
