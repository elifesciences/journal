<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;

final class ArticleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_article_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $breadcrumb = $crawler->filter('.breadcrumb-item a');
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals([
            [
                '/articles/research-article',
                'Research Article',
            ],
        ], $breadcrumb->extract(['href', '_text']));


        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertEmpty($crawler->filter('.institution_list'));
        $this->assertSame('Jan 1, 2010', trim(preg_replace('!\s+!', ' ', $crawler->filter('.content-header .meta')->text())));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
        $this->assertEmpty($crawler->filter('.institution_list'));

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
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('doi:10.7554/eLife.00001', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertSame('Article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 Bar. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));

        $this->assertEmpty($crawler->filter('meta[name^="citation_"]'));
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                    'xml' => 'http://www.example.com/xml',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'image' => [
                        'social' => [
                            'uri' => 'https://www.example.com/iiif/image/social',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://www.example.com/social.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
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
                                [
                                    'type' => 'section',
                                    'id' => 's-1-1',
                                    'title' => 'Section 1.1',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'section',
                                    'id' => 's-1-2',
                                    'title' => 'Section 1.2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'section',
                                    'id' => 's-1-3',
                                    'title' => 'Section 1.3',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 3,
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

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/image/social/full/full/0/default.jpg', $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('https://www.example.com/iiif/image/social/full/full/0/default.jpg', $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_has_citation_metadata()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                    'pdf' => 'http://www.example.com/pdf',
                    'xml' => 'http://www.example.com/xml',
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
                                        'preferred' => 'One Person',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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

        $crawler = $client->request('GET', '/articles/00001', [], [], ['HTTP_X_ELIFE_GOOGLE_SCHOLAR_METADATA' => '1']);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertNotEmpty($crawler->filter('meta[name^="citation_"]'));
        $this->assertSame('http://localhost/articles/00001.xml', $crawler->filter('meta[name="citation_xml_url"]')->attr('content'));
        $this->assertSame('Title prefix: Article title', $crawler->filter('meta[name="citation_title"]')->attr('content'));
        $this->assertSame('10.7554/eLife.00001', $crawler->filter('meta[name="citation_doi"]')->attr('content'));
        $this->assertSame('eLife 2007;1:e00001', $crawler->filter('meta[name="citation_id"]')->attr('content'));
        $this->assertSame('http://localhost/articles/00001.pdf', $crawler->filter('meta[name="citation_pdf_url"]')->attr('content'));
        $this->assertSame('http://localhost/articles/00001.xml', $crawler->filter('meta[name="citation_xml_url"]')->attr('content'));
        $this->assertSame('2007/01/01', $crawler->filter('meta[name="citation_publication_date"]')->attr('content'));
        $this->assertCount(2, $crawler->filter('meta[name="citation_author"]'));
        $this->assertCount(1, $crawler->filter('meta[name="citation_reference"]'));
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

        $this->assertEquals('http://schema.org/ScholarlyArticle', $node->getType()->getId());
        $this->assertEquals(new TypedValue('Article title', RdfConstants::XSD_STRING), $node->getProperty('http://schema.org/headline'));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
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
                        'application/vnd.elife.article-history+json; version=2',
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
        $this->assertCount(5, $crawler->filter('.author_list_item'));
        $this->assertSame('Author One', trim($crawler->filter('.author_list_item')->eq(0)->text(), " \n,"));
        $this->assertSame('Author Two', trim($crawler->filter('.author_list_item')->eq(1)->text(), " \n,"));
        $this->assertSame('Author Three', trim($crawler->filter('.author_list_item')->eq(2)->text(), " \n,"));
        $this->assertSame('Author Four', trim($crawler->filter('.author_list_item')->eq(3)->text(), " \n,"));
        $this->assertSame('on behalf of Institution Four',
            trim($crawler->filter('.author_list_item')->eq(4)->text(), " \n,"));
        $this->assertCount(3, $crawler->filter('.institution_list_item'));
        $this->assertSame('Institution One, Country One',
            trim($crawler->filter('.institution_list_item')->eq(0)->text(), " \n;"));
        $this->assertSame('Institution Two, Country Two',
            trim($crawler->filter('.institution_list_item')->eq(1)->text(), " \n;"));
        $this->assertSame('Institution Three',
            trim($crawler->filter('.institution_list_item')->eq(2)->text(), " \n;"));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
        $this->assertCount(0, $crawler->filter('.cauthor_list_item'));
        $this->assertCount(0, $crawler->filter('.institution_list_item'));

        $articleInfo = $crawler->filter('.main-content-grid > section:nth-of-type(2)');
        $this->assertSame('Article information', $articleInfo->filter('header > h2')->text());
    }

    /**
     * @test
     */
    public function it_displays_bioprotocols()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/bioprotocol/article/00001',
                ['Accept' => 'application/vnd.elife.bioprotocol+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.bioprotocol+json; version=1'],
                json_encode([
                    'total' => 2,
                    'items' => [
                        [
                            'sectionId' => 's-1-1',
                            'title' => 'Section 1.1',
                            'status' => false,
                            'uri' => 'https://www.example.com/s-1-1',
                        ],
                        [
                            'sectionId' => 's-1-3',
                            'title' => 'Section 1.3',
                            'status' => true,
                            'uri' => 'http://www.example.com/s-1-3',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $sections = $crawler->filter('.main-content-grid > .article-section:first-child > .article-section__body > .article-section');

        $this->assertContains('Request a detailed protocol', $sections->eq(0)->filter('.article-section__header_link')->text());
        $this->assertEmpty($sections->eq(1)->filter('.article-section__header_link'));
        $this->assertContains('View detailed protocol', $sections->eq(2)->filter('.article-section__header_link')->text());
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

        // @todo: Reintroduce test for comments.

        $this->assertSame(
            [
                '5,678 views',
                '1,234 citations',
            ],
            array_map(function (string $text) {
                return trim(preg_replace('!\s+!', ' ', $text));
            }, $crawler->filter('.contextual-data__item')->extract('_text'))
        );

        $metrics = $crawler->filter('.main-content-grid > section:nth-of-type(3)');
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
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
                            'role' => 'Reviewer',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
        $this->assertEmpty($crawler->filter('.view-selector'));
        $articleInfo = $crawler->filter('.main-content-grid > section:nth-of-type(1)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $authorDetails = $articleInfo->filter('div > ol:nth-of-type(1) > li.authors-details__author');
        $this->assertCount(1, $authorDetails);
        $this->assertSame('Author One', $authorDetails->eq(0)->filter('.author-details__name')->text());

        $articleInfo = $crawler->filter('.main-content-grid > section:nth-of-type(1) > div > section');

        $reviewers = $articleInfo->eq(0);
        $this->assertSame('Reviewer', $reviewers->filter('header > h3')->text());

        $publicationHistory = $articleInfo->eq(1);
        $this->assertSame('Publication history', $publicationHistory->filter('header > h3')->text());
        $this->assertCount(1, $publicationHistory->filter('ol')->children());
        $this->assertSame('Accepted Manuscript published: January 1, 2010 (version 1)', $publicationHistory->filter('ol')->children()->eq(0)->text());

        $copyright = $articleInfo->eq(2);
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Author One', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

        $this->assertSame('Download links', $crawler->filter('.main-content-grid > section:nth-of-type(2) .article-section__header_text')->text());

        $this->assertSame('Categories and tags', $crawler->filter('.main-content-grid > section:nth-of-type(3) .article-meta__group_title')->text());
    }

    /**
     * @test
     */
    public function it_displays_pdf_only_info_bar_if_no_vor_available()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
                json_encode([
                    'status' => 'poa',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 2,
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
                            'role' => 'Reviewer',
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
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions/1',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
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
                            'role' => 'Reviewer',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 2,
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
        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));

        $crawler = $client->request('GET', '/articles/00001v1');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('.info-bar'));
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));
        $this->assertContains('Read the most recent version of this article.',
            array_map('trim', $crawler->filter('.info-bar')->extract(['_text'])));
    }

    /**
     * @test
     */
    public function it_does_not_display_pdf_only_info_bar_if_vor_available()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertNotContains(
            'Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar')->eq(0)->extract(['_text']))
        );
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function it_displays_era_info_bar_when_it_has_associated_era()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl('id-of-article-with-era'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains(
            'See this research in an executable code view.',
            array_map('trim', $crawler->filter('.info-bar--warning')->extract(['_text']))
        );
        $this->assertNotEmpty($crawler->filter('.article-download-links-list__link')->selectLink('Executable version'));
        $this->assertNotEmpty($crawler->filter('.article-download-links-list__secondary_link')->selectLink('What are executable versions?'));
        $this->assertContains('/id-of-article-with-era/executable/download', $crawler->filter('.article-download-links-list__link')->selectLink('Executable version')->attr('href'));
        $this->assertContains('Executable code', $crawler->filter('.view-selector')->text());
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function it_does_not_display_era_info_bar_when_it_has_associated_era_but_it_is_not_the_latest_version()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl('id-of-article-with-era'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.info-bar'));
        $this->assertEmpty($crawler->filter('.article-download-links-list__link')->selectLink('Executable version'));
        $this->assertEmpty($crawler->filter('.view-selector'));
    }

    /**
     * @test
     */
    public function it_displays_dismissible_info_bars_when_it_has_been_selected()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl('26231'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            '<a href="https://elifesciences.org/inside-elife/4f706531/special-issue-call-for-papers-in-aging-geroscience-and-longevity">Read the call for papers</a> for the eLife Special Issue on Aging, Geroscience and Longevity.',
            $crawler->filter('.info-bar--dismissible .info-bar__text')->html()
        );
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function it_does_not_display_era_info_bar_when_it_has_no_associated_era()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl('00001'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertNotContains(
            'executable code view',
            array_map('trim', $crawler->filter('.info-bar')->eq(0)->extract(['_text']))
        );
        $this->assertEmpty($crawler->filter('.article-download-links-list__link')->selectLink('Executable version'));
        $this->assertNotContains('Executable code', $crawler->filter('.view-selector')->text());
    }

    /**
     * @test
     */
    public function it_does_not_display_dismissible_info_bars_when_it_is_a_poa_as_it_has_already_different_info_bars()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPoaUrl('26231'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertEquals(1, $crawler->filter('.info-bar')->count());
        $this->assertEquals(
            [],
            array_map('trim', $crawler->filter('.info-bar--dismissible')->extract(['_text']))
        );
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
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('doi:10.7554/eLife.00001', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertSame('Article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 Author One. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));

        $this->assertEmpty($crawler->filter('meta[name^="citation_"]'));
    }

    /**
     * @test
     */
    public function it_has_citation_metadata_on_previous_versions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl(), [], [], ['HTTP_X_ELIFE_GOOGLE_SCHOLAR_METADATA' => '1']);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertNotEmpty($crawler->filter('meta[name^="citation_"]'));
        $this->assertSame('http://localhost/articles/00001v1.pdf', $crawler->filter('meta[name="citation_pdf_url"]')->attr('content'));
        $this->assertSame('http://localhost/articles/00001v1.xml', $crawler->filter('meta[name="citation_xml_url"]')->attr('content'));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
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
                    'pdf' => 'http://www.example.com/pdf',
                    'xml' => 'http://www.example.com/xml',
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
                            'role' => 'Reviewer',
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
                                'preferred' => 'Reviewing Editor 1',
                                'index' => 'Reviewing Editor 1',
                            ],
                            'role' => 'Reviewing Editor',
                        ],
                        [
                            'name' => [
                                'preferred' => 'Senior Editor 1',
                                'index' => 'Senior Editor 1',
                            ],
                            'role' => 'Senior Editor',
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
                            'role' => 'Reviewer',
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
                    'editorEvaluation' => [
                        'doi' => '10.7554/eLife.09560.sa0',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Editor\'s evaluation text',
                            ],
                        ],
                        'scietyUri' => 'https://editor-evaluation.com',
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
                                    'text' => 'Body text <a href="#image1s1">Image 1 supplement 1</a>',
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
                    'dataSets' => [
                        'availability' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Data availability statement',
                            ],
                        ],
                        'generated' => [
                            [
                                'id' => 'dataro1',
                                'authors' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Foo Bar',
                                            'index' => 'Bar, Foo',
                                        ],
                                    ],
                                    [
                                        'type' => 'group',
                                        'name' => 'Baz',
                                    ],
                                ],
                                'date' => '2013',
                                'title' => 'Data set 1',
                                'dataId' => 'DataSet1',
                                'uri' => 'http://www.example.com/',
                                'details' => 'Data set details.',
                            ],
                        ],
                        'used' => [
                            [
                                'id' => 'dataro2',
                                'authors' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Foo Bar',
                                            'index' => 'Bar, Foo',
                                        ],
                                    ],
                                ],
                                'date' => '2014',
                                'title' => 'Data set 2',
                            ],
                        ],
                    ],
                    'references' => [
                        [
                            'type' => 'book',
                            'id' => 'bib1',
                            'date' => '2000',
                            'discriminator' => 'a',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'bookTitle' => 'Book reference',
                            'publisher' => [
                                'name' => [
                                    'A publisher',
                                ],
                            ],
                        ],
                        [
                            'type' => 'book-chapter',
                            'id' => 'bib2',
                            'date' => '1818',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'editors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'Two Person',
                                        'index' => 'Two, Person',
                                    ],
                                ],
                            ],
                            'bookTitle' => 'Book',
                            'chapterTitle' => 'Book chapter reference',
                            'pages' => 'In press',
                            'publisher' => [
                                'name' => [
                                    'A publisher',
                                ],
                            ],
                        ],
                        [
                            'type' => 'clinical-trial',
                            'id' => 'bib3',
                            'date' => '2015',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'authorsType' => 'authors',
                            'title' => 'Clinical trial reference',
                            'uri' => 'http://www.example.com/',
                        ],
                        [
                            'type' => 'conference-proceeding',
                            'id' => 'bib4',
                            'date' => '2010',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'articleTitle' => 'Conference proceeding reference',
                            'conference' => [
                                'name' => [
                                    'A conference',
                                ],
                            ],
                        ],
                        [
                            'type' => 'data',
                            'id' => 'bib5',
                            'date' => '2015',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Data reference',
                            'source' => 'A source',
                        ],
                        [
                            'type' => 'journal',
                            'id' => 'bib6',
                            'date' => '2013',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'articleTitle' => 'Journal article reference',
                            'journal' => 'A journal',
                        ],
                        [
                            'type' => 'patent',
                            'id' => 'bib7',
                            'date' => '2011',
                            'inventors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Patent reference',
                            'patentType' => 'United States patent',
                            'country' => 'United States',
                        ],
                        [
                            'type' => 'periodical',
                            'id' => 'bib8',
                            'date' => '2013-10-19',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'articleTitle' => 'Periodical article reference',
                            'periodical' => 'A periodical',
                            'pages' => 'In press',
                        ],
                        [
                            'type' => 'preprint',
                            'id' => 'bib9',
                            'date' => '2013',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'articleTitle' => 'Preprint reference',
                            'source' => 'A repository',
                            'uri' => 'http://www.example.com/',
                        ],
                        [
                            'type' => 'report',
                            'id' => 'bib10',
                            'date' => '2016',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Report reference',
                            'publisher' => [
                                'name' => [
                                    'A publisher',
                                ],
                            ],
                        ],
                        [
                            'type' => 'software',
                            'id' => 'bib11',
                            'date' => '2011',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Software reference',
                            'publisher' => [
                                'name' => [
                                    'A publisher',
                                ],
                            ],
                        ],
                        [
                            'type' => 'thesis',
                            'id' => 'bib12',
                            'date' => '2006',
                            'author' => [
                                'name' => [
                                    'preferred' => 'One Person',
                                    'index' => 'One, Person',
                                ],
                            ],
                            'title' => 'Thesis reference',
                            'publisher' => [
                                'name' => [
                                    'A publisher',
                                ],
                            ],
                        ],
                        [
                            'type' => 'web',
                            'id' => 'bib13',
                            'date' => '2014',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Webpage reference',
                            'uri' => 'http://www.example.com/',
                        ],
                        [
                            'type' => 'unknown',
                            'id' => 'bib14',
                            'date' => '2014',
                            'authors' => [
                                [
                                    'type' => 'person',
                                    'name' => [
                                        'preferred' => 'One Person',
                                        'index' => 'One, Person',
                                    ],
                                ],
                            ],
                            'title' => 'Unknown reference',
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
                        'id' => 'decision-letter-id',
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
                        'id' => 'author-response-id',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'received' => '2006-12-28',
                    'accepted' => '2006-12-30',
                    'versions' => [
                        [
                            'status' => 'preprint',
                            'description' => 'This manuscript was published as a preprint at bioRxiv.',
                            'uri' => 'https://doi.org/10.1101/2006.12.27',
                            'date' => '2006-12-27T00:00:00Z',
                        ],
                        [
                            'status' => 'preprint',
                            'description' => 'This manuscript was published as a preprint at bioRxiv.',
                            'uri' => 'https://doi.org/10.1101/2006.12.29',
                            'date' => '2006-12-29T00:00:00Z',
                        ],
                        [
                            'status' => 'preprint',
                            'description' => 'This manuscript was published as a preprint at bioRxiv.',
                            'uri' => 'https://doi.org/10.1101/2006.12.31',
                            'date' => '2006-12-31T00:00:00Z',
                        ],
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

        $this->assertSame('Title prefix: Article title', $crawler->filter('.content-header__title')->text());

        $this->assertSame('Abstract',
            $crawler->filter('.main-content-grid > section:nth-of-type(1) > header > h2')->text());
        $this->assertSame('Abstract text',
            $crawler->filter('.main-content-grid > section:nth-of-type(1) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.001',
            trim($crawler->filter('.main-content-grid > section:nth-of-type(1) > div > .doi')->text()));
        $this->assertSame('Editor\'s evaluation',
            $crawler->filter('.main-content-grid > section:nth-of-type(2) > header > h2')->text());
        $this->assertSame('Editor\'s evaluation text',
            $crawler->filter('.main-content-grid > section:nth-of-type(2) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.sa0',
            trim($crawler->filter('.main-content-grid > section:nth-of-type(2) > div > .doi')->text()));
        $this->assertSame(
            [
                [
                    'Decision letter',
                    '/articles/00001#decision-letter-id',
                ],
                [
                    'Reviews on Sciety',
                    'https://editor-evaluation.com',
                ],
                [
                    'eLife\'s review process',
                    '/about/peer-review',
                ],
            ],
            $crawler->filter('.main-content-grid > section:nth-of-type(2) > div .article-section__related_link')->extract(['_text', 'href'])
        );
        $this->assertSame('eLife digest',
            $crawler->filter('.main-content-grid > section:nth-of-type(3) > header > h2')->text());
        $this->assertSame('Digest text',
            $crawler->filter('.main-content-grid > section:nth-of-type(3) > div > p')->text());
        $this->assertSame('https://doi.org/10.7554/eLife.09560.002',
            trim($crawler->filter('.main-content-grid > section:nth-of-type(3) > div > .doi')->text()));
        $this->assertSame('Body title',
            $crawler->filter('.main-content-grid > section:nth-of-type(4) > header > h2')->text());

        $body = $crawler->filter('.main-content-grid > section:nth-of-type(4) > div')->children();
        $this->assertCount(2, $body);

        $this->assertSame('Body text Image 1 supplement 1', $body->eq(0)->text());
        $this->assertSame('/articles/00001/figures#image1s1', $body->eq(0)->filter('a')->attr('href'));
        $this->assertSame('Image 1 label with 1 supplement see all', trim($body->eq(1)->filter('.asset-viewer-inline__header_text')->text()));
        $this->assertSame('/articles/00001/figures#image1', $body->eq(1)->filter('.asset-viewer-inline__header_link')->attr('href'));

        $appendix = $crawler->filter('.main-content-grid > section:nth-of-type(5)');
        $this->assertSame('Appendix 1', $appendix->filter('header > h2')->text());
        $this->assertSame('Appendix title', $appendix->filter('div > section > header > h3')->text());
        $this->assertSame('Appendix text', $appendix->filter('div > p')->text());
        $dataAvailability = $crawler->filter('.main-content-grid > section:nth-of-type(6)');
        $this->assertSame('Data availability',
            $dataAvailability->filter('header > h2')->text());
        $data = $dataAvailability->filter('.article-section__body')->children();
        $this->assertSame('Data availability statement', trim($data->eq(0)->text()));
        $this->assertSame('The following data sets were generated', trim($data->eq(1)->text()));
        $this->assertSame('Data set 1', trim($data->eq(2)->filter('.reference__title')->text()));
        $this->assertSame('The following previously published data sets were used', trim($data->eq(3)->text()));
        $this->assertSame('Data set 2', trim($data->eq(4)->filter('.reference__title')->text()));
        $references = $crawler->filter('.main-content-grid > section:nth-of-type(7)');
        $this->assertSame('References',
            $references->filter('header > h2')->text());
        $this->assertSame('Book reference',
            $references->filter('div > ol > li:nth-of-type(1) .reference__title')->text());
        $this->assertSame('Book',
            $references->filter('div > ol > li:nth-of-type(1) .reference__label')->text());
        $this->assertSame('Book chapter reference',
            $references->filter('div > ol > li:nth-of-type(2) .reference__title')->text());
        $this->assertSame('Book',
            $references->filter('div > ol > li:nth-of-type(2) .reference__label')->text());
        $this->assertSame('Clinical trial reference',
            $references->filter('div > ol > li:nth-of-type(3) .reference__title')->text());
        $this->assertSame('Clinical Trial',
            $references->filter('div > ol > li:nth-of-type(3) .reference__label')->text());
        $this->assertSame('Conference proceeding reference',
            $references->filter('div > ol > li:nth-of-type(4) .reference__title')->text());
        $this->assertSame('Conference',
            $references->filter('div > ol > li:nth-of-type(4) .reference__label')->text());
        $this->assertSame('Data reference',
            $references->filter('div > ol > li:nth-of-type(5) .reference__title')->text());
        $this->assertSame('Data',
            $references->filter('div > ol > li:nth-of-type(5) .reference__label')->text());
        $this->assertSame('Journal article reference',
            $references->filter('div > ol > li:nth-of-type(6) .reference__title')->text());
        $this->assertEmpty($references->filter('div > ol > li:nth-of-type(6) .reference__label'));
        $this->assertSame('Patent reference',
            $references->filter('div > ol > li:nth-of-type(7) .reference__title')->text());
        $this->assertSame('Patent',
            $references->filter('div > ol > li:nth-of-type(7) .reference__label')->text());
        $this->assertSame('Periodical article reference',
            $references->filter('div > ol > li:nth-of-type(8) .reference__title')->text());
        $this->assertSame('Periodical',
            $references->filter('div > ol > li:nth-of-type(8) .reference__label')->text());
        $this->assertSame('Preprint reference',
            $references->filter('div > ol > li:nth-of-type(9) .reference__title')->text());
        $this->assertSame('Preprint',
            $references->filter('div > ol > li:nth-of-type(9) .reference__label')->text());
        $this->assertSame('Report reference',
            $references->filter('div > ol > li:nth-of-type(10) .reference__title')->text());
        $this->assertSame('Report',
            $references->filter('div > ol > li:nth-of-type(10) .reference__label')->text());
        $this->assertSame('Software reference',
            $references->filter('div > ol > li:nth-of-type(11) .reference__title')->text());
        $this->assertSame('Software',
            $references->filter('div > ol > li:nth-of-type(11) .reference__label')->text());
        $this->assertSame('Thesis reference',
            $references->filter('div > ol > li:nth-of-type(12) .reference__title')->text());
        $this->assertSame('Thesis',
            $references->filter('div > ol > li:nth-of-type(12) .reference__label')->text());
        $this->assertSame('Webpage reference',
            $references->filter('div > ol > li:nth-of-type(13) .reference__title')->text());
        $this->assertSame('Website',
            $references->filter('div > ol > li:nth-of-type(13) .reference__label')->text());
        $this->assertSame('Unknown reference',
            $references->filter('div > ol > li:nth-of-type(14) .reference__title')->text());
        $this->assertEmpty($references->filter('div > ol > li:nth-of-type(14) .reference__label'));
        $this->assertSame('Decision letter', $crawler->filter('#decision-letter-id h2')->text());
        $this->assertSame('Decision letter',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > header > h2')->text());
        $this->assertCount(4, $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name'));
        $this->assertSame('Reviewer 1',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(0)->text());
        $this->assertSame('Reviewing Editor 1',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(1)->text());
        $this->assertSame('Senior Editor 1',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(2)->text());
        $this->assertSame('Reviewer 2',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(3)->text());
        $this->assertSame('Decision letter description',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .decision-letter-header__main_text > p')->text());
        $this->assertSame('Decision letter text',
            $crawler->filter('.main-content-grid > section:nth-of-type(8) > div > p')->text());
        $this->assertSame('Author response', $crawler->filter('#author-response-id h2')->text());
        $this->assertSame('Author response',
            $crawler->filter('.main-content-grid > section:nth-of-type(9) > header > h2')->text());
        $this->assertSame('Author response text',
            $crawler->filter('.main-content-grid > section:nth-of-type(9) > div > p')->text());

        $articleInfo = $crawler->filter('.main-content-grid > section:nth-of-type(10)');
        $this->assertSame('Article and author information',
            $articleInfo->filter('header > h2')->text());

        $authorDetails = $articleInfo->filter('div > ol:nth-of-type(1) > li.authors-details__author');
        $this->assertCount(2, $authorDetails);
        $this->assertSame('Foo Bar, Role', $authorDetails->eq(0)->filter('.author-details__name')->text());
        $this->assertSame('Baz', $authorDetails->eq(1)->filter('.author-details__name')->text());

        $articleInfo = $crawler->filter('.main-content-grid > section:nth-of-type(10) > div > section');

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
        $this->assertSame('Senior Editor', $reviewers->filter('header > h3')->text());

        $reviewerDetails = $reviewers->filter('li');
        $this->assertCount(1, $reviewerDetails);
        $this->assertSame('Senior Editor 1, Institution, Country', $reviewerDetails->eq(0)->text());

        $reviewers = $articleInfo->eq(4);
        $this->assertSame('Reviewing Editor', $reviewers->filter('header > h3')->text());

        $reviewerDetails = $reviewers->filter('li');
        $this->assertCount(1, $reviewerDetails);
        $this->assertSame('Reviewing Editor 1', $reviewerDetails->eq(0)->text());

        $reviewers = $articleInfo->eq(5);
        $this->assertSame('Reviewers', $reviewers->filter('header > h3')->text());

        $reviewerDetails = $reviewers->filter('li');
        $this->assertCount(2, $reviewerDetails);
        $this->assertSame('Reviewer 1, Institution, Country', $reviewerDetails->eq(0)->text());
        $this->assertSame('Reviewer 2', $reviewerDetails->eq(1)->text());

        $publicationHistory = $articleInfo->eq(6);
        $this->assertSame('Publication history', $publicationHistory->filter('header > h3')->text());
        $this->assertCount(9, $publicationHistory->filter('ol')->children());
        $this->assertSame('Preprint posted: December 27, 2006 (view preprint)', $publicationHistory->filter('ol')->children()->eq(0)->text());
        $this->assertSame('Received: December 28, 2006', $publicationHistory->filter('ol')->children()->eq(1)->text());
        $this->assertSame('Preprint posted: December 29, 2006 (view preprint)', $publicationHistory->filter('ol')->children()->eq(2)->text());
        $this->assertSame('Accepted: December 30, 2006', $publicationHistory->filter('ol')->children()->eq(3)->text());
        $this->assertSame('Preprint posted: December 31, 2006 (view preprint)', $publicationHistory->filter('ol')->children()->eq(4)->text());
        $this->assertSame('Accepted Manuscript published: January 1, 2007 (version 1)', $publicationHistory->filter('ol')->children()->eq(5)->text());
        $this->assertSame('Accepted Manuscript updated: January 1, 2008 (version 2)', $publicationHistory->filter('ol')->children()->eq(6)->text());
        $this->assertSame('Version of Record published: January 1, 2009 (version 3)', $publicationHistory->filter('ol')->children()->eq(7)->text());
        $this->assertSame('Version of Record updated: January 1, 2010 (version 4)', $publicationHistory->filter('ol')->children()->eq(8)->text());

        $copyright = $articleInfo->eq(7);
        $this->assertSame('Copyright', $copyright->filter('header > h3')->text());
        $this->assertContains('© 2012, Bar', $copyright->filter('div')->text());
        $this->assertContains('Copyright statement.', $copyright->filter('div')->text());

        $downloadLinks = $crawler->filter('.main-content-grid > section:nth-of-type(11)');
        $this->assertSame('Download links', $downloadLinks->filter('.article-section__header_text')->text());
        $downloadLinksGroup = $downloadLinks->filter('.article-download-links-list__group');
        $this->assertCount(3, $downloadLinksGroup);

        $pdfs = $downloadLinksGroup->eq(0);
        $this->assertSame('Downloads (link to download the article as PDF)', $pdfs->filter('.article-download-links-list__heading')->text());
        $pdfLinks = $pdfs->filter('.article-download-links-list__item');
        $this->assertCount(1, $pdfLinks);
        $this->assertSame('Article PDF', $this->crawlerText($pdfLinks->eq(0)));

        $openCitations = $downloadLinksGroup->eq(1);
        $this->assertSame('Open citations (links to open the citations from this article in various online reference manager services)', $openCitations->filter('.article-download-links-list__heading')->text());
        $openCitationLinks = $openCitations->filter('.article-download-links-list__item');
        $this->assertCount(2, $openCitationLinks);
        $this->assertSame('Mendeley', $this->crawlerText($openCitationLinks->eq(0)));
        $this->assertCount(1, $openCitationLinks->filter('[data-behaviour="CheckPMC"]'));

        $citeThisArticle = $downloadLinksGroup->eq(2);
        $this->assertSame('Cite this article (links to download the citations from this article in formats compatible with various reference manager tools)', $citeThisArticle->filter('.article-download-links-list__heading')->text());
        $this->assertSame('Foo Bar Baz (2007) Title prefix: Article title eLife 1:e00001. https://doi.org/10.7554/eLife.00001', $this->crawlerText($citeThisArticle->filter('.reference')));
        $citeThisArticleLinks = $citeThisArticle->filter('.article-download-links-list__item');
        $this->assertCount(2, $citeThisArticleLinks);
        $this->assertSame('BibTeX', $this->crawlerText($citeThisArticleLinks->eq(0)));
        $this->assertSame('RIS', $this->crawlerText($citeThisArticleLinks->eq(1)));

        $this->assertSame('Categories and tags', $crawler->filter('.main-content-grid > section:nth-of-type(12) .article-meta__group_title')->text());

        $this->assertRegexp('|^https://.*/00001$|', $crawler->filter('.view-selector')->attr('data-side-by-side-link'));

        $this->assertSame(
            [
                [
                    'Article',
                    '/articles/00001#content',
                ],
                [
                    'Figures and data',
                    '/articles/00001/figures#content',
                ],
            ],
            $crawler->filter('.view-selector__link')->extract(['_text', 'href'])
        );

        $this->assertSame(
            [
                'Abstract',
                'Editor\'s evaluation',
                'eLife digest',
                'Body title',
                'Appendix 1',
                'Data availability',
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
    public function it_may_have_a_data_availability_statement()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article 1 title',
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
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Foo Bar',
                                'index' => 'Bar, Foo',
                            ],
                        ],
                    ],
                    'dataSets' => [
                        'availability' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Data availability statement',
                            ],
                        ],
                        'generated' => [
                            [
                                'id' => 'dataro1',
                                'authors' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Foo Bar',
                                            'index' => 'Bar, Foo',
                                        ],
                                    ],
                                    [
                                        'type' => 'group',
                                        'name' => 'Baz',
                                    ],
                                ],
                                'date' => '2013',
                                'title' => 'Data set 1',
                                'dataId' => 'DataSet1',
                                'uri' => 'http://www.example.com/',
                                'details' => 'Data set details.',
                            ],
                        ],
                        'used' => [
                            [
                                'id' => 'dataro2',
                                'authors' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Foo Bar',
                                            'index' => 'Bar, Foo',
                                        ],
                                    ],
                                ],
                                'date' => '2014',
                                'title' => 'Data set 2',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article 1 title',
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

        $crawler = $client->request('GET', '/articles/00001');

        $this->assertSame(
            [
                [
                    'Article',
                    '/articles/00001#content',
                ],
            ],
            $crawler->filter('.view-selector__link')->extract(['_text', 'href'])
        );

        $dataAvailability = $crawler->filter('.main-content-grid > section:nth-of-type(2)');
        $this->assertSame('Data availability',
            $dataAvailability->filter('header > h2')->text());

        $data = $dataAvailability->filter('.article-section__body')->children();
        $this->assertSame('Data availability statement', trim($data->eq(0)->text()));
        $this->assertSame('The following data sets were generated', trim($data->eq(1)->text()));
        $this->assertSame('Data set 1', trim($data->eq(2)->filter('.reference__title')->text()));
        $this->assertSame('The following previously published data sets were used', trim($data->eq(3)->text()));
        $this->assertSame('Data set 2', trim($data->eq(4)->filter('.reference__title')->text()));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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
                        'application/vnd.elife.recommendations+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.recommendations+json; version=2'],
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
                            'abstract' => [
                                'content' => [
                                    [
                                        'type' => 'section',
                                        'title' => 'Introduction',
                                        'content' => [
                                            [
                                                'type' => 'paragraph',
                                                'text' => 'Abstract 00007.',
                                            ],
                                        ],
                                    ],
                                ],
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

    protected function getUrl($articleId = '00001') : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/{$articleId}",
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=6'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => $articleId,
                    'version' => 3,
                    'type' => 'research-article',
                    'doi' => "10.7554/eLife.{$articleId}",
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00Z',
                    'versionDate' => '2012-01-01T00:00:00Z',
                    'statusDate' => '2011-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => "e{$articleId}",
                    'xml' => 'http://www.example.com/xml',
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
                                [
                                    'type' => 'section',
                                    'id' => 's-1-1',
                                    'title' => 'Section 1.1',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'section',
                                    'id' => 's-1-2',
                                    'title' => 'Section 1.2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'section',
                                    'id' => 's-1-3',
                                    'title' => 'Section 1.3',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Content',
                                        ],
                                    ],
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
                "http://api.elifesciences.org/articles/{$articleId}/versions",
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => $articleId,
                            'version' => 3,
                            'type' => 'research-article',
                            'doi' => "10.7554/eLife.{$articleId}",
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => "e{$articleId}",
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

        return "/articles/{$articleId}";
    }

    private function getPreviousVersionUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions/1',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
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
                    'pdf' => 'http://www.example.com/pdf',
                    'xml' => 'http://www.example.com/xml',
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
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
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

    public function getPoaUrl($articleId = '12345') : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/${articleId}",
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=6']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-poa+json; version=3'],
                json_encode([
                    'status' => 'poa',
                    'stage' => 'published',
                    'id' => $articleId,
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => "10.7554/eLife.{$articleId}",
                    'title' => 'Article title',
                    'published' => '2010-01-01T00:00:00Z',
                    'versionDate' => '2010-01-01T00:00:00Z',
                    'statusDate' => '2010-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => "e{$articleId}",
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
                            'role' => 'Reviewer',
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
                    ],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/{$articleId}/versions",
                [
                    'Accept' => [
                        'application/vnd.elife.article-history+json; version=2',
                    ],
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-history+json; version=2'],
                json_encode([
                    'versions' => [
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => $articleId,
                            'version' => 1,
                            'type' => 'research-article',
                            'doi' => "10.7554/eLife.{$articleId}",
                            'title' => 'Article title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => "e{$articleId}",
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

        return "/articles/{$articleId}";
    }
}
