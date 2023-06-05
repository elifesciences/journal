<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ML\JsonLD\JsonLD;
use ML\JsonLD\RdfConstants;
use ML\JsonLD\TypedValue;
use Traversable;

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
                'Research Article',
                '/articles/research-article',
            ],
        ], $breadcrumb->extract(['_text', 'href']));


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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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

        $this->assertSame('Article title eLife 1:e00001. https://doi.org/10.7554/eLife.00001', $this->crawlerText($crawler->filter('.article-download-links-list__group .reference')));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
        $authors = $crawler->filter('.content-header .author_list_item');
        $this->assertCount(5, $authors);
        $this->assertSame('Author One', trim($authors->eq(0)->text(), " \n,"));
        $this->assertSame('Author Two', trim($authors->eq(1)->text(), " \n,"));
        $this->assertSame('Author Three', trim($authors->eq(2)->text(), " \n,"));
        $this->assertSame('Author Four', trim($authors->eq(3)->text(), " \n,"));
        $this->assertSame('on behalf of Institution Four',
            trim($authors->eq(4)->text(), " \n,"));
        $institutions = $crawler->filter('.content-header .institution_list_item');
        $this->assertCount(3, $institutions);
        $this->assertSame('Institution One, Country One',
            trim($institutions->eq(0)->text(), " \n;"));
        $this->assertSame('Institution Two, Country Two',
            trim($institutions->eq(1)->text(), " \n;"));
        $this->assertSame('Institution Three',
            trim($institutions->eq(2)->text(), " \n;"));
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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

        $this->assertSame('Comment Open annotations (there are currently 0 annotations on this page).',
            $this->crawlerText($crawler->filter('.content-aside .button-collection .button-collection__item')->eq(3)));

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

    public function contextualDataMetricsProvider() : array
    {
        return [
            '0 citations and 0 page views' => [
                [
                    [
                        'service' => 'Service One',
                        'uri' => 'http://www.example.com/',
                        'citations' => 0,
                    ],
                    [
                        'service' => 'Service Two',
                        'uri' => 'http://www.example.com/',
                        'citations' => 0,
                    ],
                ],
                [
                    'totalPeriods' => 2,
                    'totalValue' => 0,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => 0,
                        ],
                        [
                            'period' => '2016-01-02',
                            'value' => 0,
                        ],
                    ],
                ],
                [],
            ],
            '0 citations and 3 views' => [
                [
                    [
                        'service' => 'Service One',
                        'uri' => 'http://www.example.com/',
                        'citations' => 0,
                    ],
                    [
                        'service' => 'Service Two',
                        'uri' => 'http://www.example.com/',
                        'citations' => 0,
                    ],
                ],
                [
                    'totalPeriods' => 2,
                    'totalValue' => 3,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => 1,
                        ],
                        [
                            'period' => '2016-01-02',
                            'value' => 2,
                        ],
                    ],
                ],
                [
                    '3 views',
                ],
            ],
            '4 citations and 0 views' => [
                [
                    [
                        'service' => 'Service One',
                        'uri' => 'http://www.example.com/',
                        'citations' => 4,
                    ],
                    [
                        'service' => 'Service Two',
                        'uri' => 'http://www.example.com/',
                        'citations' => 3,
                    ],
                ],
                [
                    'totalPeriods' => 2,
                    'totalValue' => 0,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => 0,
                        ],
                        [
                            'period' => '2016-01-02',
                            'value' => 0,
                        ],
                    ],
                ],
                [
                    '4 citations',
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider contextualDataMetricsProvider
     */
    public function it_may_display_contextual_data_metrics(array $citations, array $pageViews, array $expected)
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
                json_encode($citations)
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
                json_encode($pageViews)
            )
        );

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());

        if (empty($expected)) {
            $this->assertEmpty($crawler->filter('.contextual-data__item'));
        } else {
            $this->assertSame(
                $expected,
                array_map(function (string $text) {
                    return trim(preg_replace('!\s+!', ' ', $text));
                }, $crawler->filter('.contextual-data__item')->extract('_text'))
            );
        }
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
        $this->assertCount(1, $crawler->filter('.info-bar--info'));
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar--info')->extract(['_text'])));

        $crawler = $client->request('GET', '/articles/00001v1');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('.info-bar--info'));
        $this->assertCount(1, $crawler->filter('.info-bar--multiple-versions'));
        $this->assertContains('Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar--info')->extract(['_text'])));
        $this->assertContains('Read the most recent version of this article.',
            array_map('trim', $crawler->filter('.info-bar--multiple-versions')->extract(['_text'])));
    }

    /**
     * @test
     */
    public function it_does_not_display_pdf_only_info_bar_if_vor_available()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(0, $crawler->filter('.info-bar--info'));
        $this->assertNotContains(
            'Accepted manuscript, PDF only. Full online edition to follow.',
            array_map('trim', $crawler->filter('.info-bar--info')->eq(0)->extract(['_text']))
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
        $this->assertContains('Executable code', $crawler->filter('.tabbed-navigation')->text());
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
        $this->assertCount(0, $crawler->filter('.info-bar--info'));
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
            $crawler->filter('.info-bar--dismissible .info-bar__text')->eq(0)->html()
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
        $this->assertNotContains('Executable code', $crawler->filter('.tabbed-navigation')->text());
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
            array_map('trim', $crawler->filter('.info-bar--dismissible')->eq(1)->extract(['_text']))
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
        $this->assertEmpty($crawler->filter('.content-header__impact-statement'));

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
        $this->assertSame('Download BibTeX', $this->crawlerText($citeThisArticleLinks->eq(0)));
        $this->assertSame('Download .RIS', $this->crawlerText($citeThisArticleLinks->eq(1)));

        $this->assertSame('Categories and tags', $crawler->filter('.main-content-grid > section:nth-of-type(12) .article-meta__group_title')->text());

        $this->assertSame(
            [
                [
                    'Full text',
                    '/articles/00001#content',
                ],
                [
                    'Figures and data',
                    '/articles/00001/figures#content',
                ],
            ],
            $crawler->filter('.tabbed-navigation__tab-label a')->extract(['_text', 'href'])
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
            array_map('trim', $crawler->filter('.jump-menu__item')->extract('_text'))
        );
    }

    public function magazineArticlesProvider() : array
    {
        return [
            'insight' => [
                '79593',
                'insight',
                'Listeria monocytogenes uses respiration to sustain a risky fermentative lifestyle during infection.',
                [
                    [
                        'Magazine',
                        '/magazine',
                    ],
                    [
                        'Insight',
                        '/articles/insight',
                    ],
                ],
                [],
                [],
            ],
            'editorial' => [
                '79594',
                'editorial',
                'Listeria monocytogenes uses respiration to sustain a risky fermentative lifestyle during infection.',
                [
                    [
                        'Magazine',
                        '/magazine',
                    ],
                    [
                        'Editorial',
                        '/articles/editorial',
                    ],
                ],
                [],
                [],
            ],
            'feature' => [
                '79595',
                'feature',
                '',
                [
                    [
                        'Magazine',
                        '/magazine',
                    ],
                    [
                        'Feature Article',
                        '/articles/feature',
                    ],
                ],
                [
                    [
                        'Full text',
                        '/articles/79595#content',
                    ],
                    [
                        'Figures and data',
                        '/articles/79595/figures#content',
                    ],
                ],
                [
                    [
                        'Abstract',
                        '#abstract',
                    ],
                    [
                        'Main text',
                        '#s0',
                    ],
                    [
                        'References',
                        '#references',
                    ],
                    [
                        'Article and author information',
                        '#info',
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider magazineArticlesProvider
     */
    public function it_displays_magazine_content(
        string $id,
        string $type,
        string $expectImpactStatement,
        array $expectedBreadcrumb,
        array $expectedTabbedNavigation,
        array $expectedJumpMenu
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id,
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
                json_encode([
                    'status' => 'vor',
                    'id' => $id,
                    'version' => 1,
                    'type' => $type,
                    'doi' => '10.7554/eLife.'.$id,
                    'authorLine' => 'Lauren C Radlinski, Andreas J Bäumler',
                    'title' => 'To breathe or not to breathe?',
                    'titlePrefix' => 'Respiro-Fermentation',
                    'published' => '2022-05-20T00:00:00Z',
                    'versionDate' => '2022-05-20T00:00:00Z',
                    'volume' => 11,
                    'elocationId' => 'e'.$id,
                    'pdf' => 'https://cdn.elifesciences.org/articles/'.$id.'/elife-'.$id.'-v1.pdf',
                    'xml' => 'https://cdn.elifesciences.org/articles/'.$id.'/elife-'.$id.'-v1.xml',
                    'subjects' => [
                        [
                            'id' => 'biochemistry-chemical-biology',
                            'name' => 'Biochemistry and Chemical Biology',
                        ],
                        [
                            'id' => 'microbiology-infectious-disease',
                            'name' => 'Microbiology and Infectious Disease',
                        ],
                    ],
                    'abstract' => [
                        'content' => [
                            [
                                'text' => '<i>Listeria monocytogenes</i> uses respiration to sustain a risky fermentative lifestyle during infection.',
                                'type' => 'paragraph',
                            ],
                        ],
                    ],
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Radlinski and Bäumler',
                        'statement' => 'This article is distributed under the terms of the <a href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution License</a>, which permits unrestricted use and redistribution provided that the original author and source are credited.',
                    ],
                    'authors' => [
                        [
                            'affiliations' => [
                                [
                                    'address' => [
                                        'components' => [
                                            'country' => 'United States',
                                            'locality' => [
                                                'Davis',
                                            ],
                                        ],
                                        'formatted' => [
                                            'Davis',
                                            'United States',
                                        ],
                                    ],
                                    'name' => [
                                        'Department of Medical Microbiology and Immunology, School of Medicine, University of California, Davis',
                                    ],
                                ],
                            ],
                            'biography' => [
                                [
                                    'text' => '<b>Lauren C Radlinski</b> is in the Department of Medical Microbiology and Immunology, School of Medicine, University of California, Davis, Davis, United States',
                                    'type' => 'paragraph',
                                ],
                            ],
                            'competingInterests' => 'No competing interests declared',
                            'name' => [
                                'index' => 'Radlinski, Lauren C',
                                'preferred' => 'Lauren C Radlinski',
                            ],
                            'type' => 'person',
                        ],
                        [
                            'affiliations' => [
                                [
                                    'address' => [
                                        'components' => [
                                            'country' => 'United States',
                                            'locality' => [
                                                'Davis',
                                            ],
                                        ],
                                        'formatted' => [
                                            'Davis',
                                            'United States',
                                        ],
                                    ],
                                    'name' => [
                                        'Department of Medical Microbiology and Immunology, School of Medicine, University of California, Davis',
                                    ],
                                ],
                            ],
                            'biography' => [
                                [
                                    'text' => '<b>Andreas J Bäumler</b> is in the Department of Medical Microbiology and Immunology, School of Medicine, University of California, Davis, Davis, United States',
                                    'type' => 'paragraph',
                                ],
                            ],
                            'competingInterests' => 'No competing interests declared',
                            'emailAddresses' => [
                                'ajbaumler@ucdavis.edu',
                            ],
                            'name' => [
                                'index' => 'Bäumler, Andreas J',
                                'preferred' => 'Andreas J Bäumler',
                            ],
                            'orcid' => '0000-0001-9152-7809',
                            'type' => 'person',
                        ],
                    ],
                    'impactStatement' => '<i>Listeria monocytogenes</i> uses respiration to sustain a risky fermentative lifestyle during infection.',
                    'keywords' => [
                        'bacterial pathogenesis',
                        'cellular respiration',
                        'microbial metabolism',
                    ],
                    'body' => [
                        [
                            'content' => [
                                [
                                    'text' => 'Bacteria are masters at tuning their metabolism to thrive in diverse environments, including during infection. Constant, life-or-death competition with host immune systems and other microorganisms rewards species that maximize the amount of energy they derive from limited resources.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'Living organisms produce energy in the form of a small molecule called adenosine triphosphate (ATP). ATP is generated by breaking down, or oxidizing, high energy molecules such as sugars through a series of electron transfer reactions. These redox (reduction/oxidation) reactions require an intermediate electron carrier such as nicotine adenine dinucleotide (NADH) that must be re-oxidized (NAD<sup>+</sup>) in order for the cell to continue producing ATP by oxidizing high-energy electron donors.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'During respiration, NAD<sup>+</sup> is regenerated when electrons are transferred to a terminal electron acceptor such as oxygen. In organisms that cannot respire, ATP is produced through a less energy-efficient process called fermentation. Fermenting organisms also oxidize high-energy electron donors to produce ATP: however their strategy for regenerating NAD<sup>+</sup> requires depositing electrons on an organic molecule such as pyruvate. Thus, fermenting organisms sacrifice potential ATP by producing waste products that are not fully oxidized.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => '<i>Listeria monocytogenes</i> is an important foodborne pathogen with an unusual metabolic strategy that falls somewhere between respiration and fermentation. This bacterium carries the genes for two respiratory electron transport chains that can use either oxygen or an extracellular metabolite such as fumarate or iron as a terminal electron acceptor (<a href="#bib2">Corbett et al., 2017</a>; <a href="#bib3">Light et al., 2019</a>). However, unlike most respiring organisms, <i>L. monocytogenes</i> lacks the enzymes required to fully oxidize sugars and instead produces partially reduced fermentative end products including lactic and acetic acid (<a href="#bib9">Trivett and Meyer, 1971</a>). Despite this, respiration is absolutely essential for <i>L. monocytogenes</i> to cause disease (<a href="#bib2">Corbett et al., 2017</a>) – in fact, mutants that cannot respire are considered safe enough to be used in vaccine development (<a href="#bib7">Stritzker et al., 2004</a>).',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'Now, in eLife, Samuel Light (University of Chicago) and colleagues – including Rafael Rivera-Lugo and David Deng (both from the University of California at Berkeley) as joint first authors – report on why respiration is essential for an organism that gets its energy through fermentation (<a href="#bib5">Rivera-Lugo et al., 2022</a>).',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'By comparing the waste products <i>L. monocytogenes</i> generates in the presence or absence of oxygen (a terminal electron acceptor), Rivera-Lugo et al. observed that oxygen shifts the composition of <i>L. monocytogenes</i> fermentative end products from primarily lactic to exclusively acetic acid. Compared to lactic acid, acetic acid is a slightly more oxidized waste product, the production of which generates more ATP but insufficient NAD<sup>+</sup> to sustain itself. Thus, while acetic acid production generates more energy, it comes at the cost of redox balance, which could incur a potential reduction in cellular viability. Indeed, Rivera-Lugo et al. observed that genetically disrupting respiratory pathways inhibited <i>L. monocytogenes</i> growth within immune cells and prevented the bacterium from causing disease in mice.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'Based on these results, Rivera-Lugo et al. surmised that <i>L. monocytogenes</i> relies on respiration either to re-oxidize the surplus NADH that results from acetic acid fermentation (and re-establish redox balance), or to generate proton motive force (PMF). PMF is generated when protons are pumped across the bacterial membrane during respiration. The energy stored in the resulting proton gradient can be used by the cell to power important cellular activities such as solute transport and motility.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'Redox balance and PMF generation are difficult processes to separate as they involve the same cellular machinery. To determine which of the two is essential for the pathogenesis of <i>L. monocytogenes</i>, Rivera-Lugo et al. genetically modified the bacterium to express an unusual NADH oxidase (NOX) previously described in the bacterium <i>Lactococcus lactis</i> (<a href="#bib4">Neves et al., 2002</a>). NOX decouples the production of NAD<sup>+</sup> and PMF by transferring electrons from NADH directly to oxygen without pumping protons across the membrane (<a href="#bib8">Titov et al., 2016</a>). This tool allowed the team to separate these two processes by restoring NAD<sup>+</sup> regeneration, without increasing PMF. When Rivera-Lugo et al. expressed NOX in a <i>L. monocytogenes</i> mutant that cannot respire, NOX activity restored acetic acid production, intracellular growth, cell-to-cell spread, and pathogenesis of the bacterium. This result implies that the primary purpose for <i>L. monocytogenes</i> respiration during infection is NAD<sup>+</sup> regeneration, not PMF production.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'While balancing redox may allow <i>L. monocytogenes</i> to produce more ATP through the fermentation of acetic acid, it does not explain why a respiration-deficient mutant cannot grow in a host. During infection, <i>L. monocytogenes</i> infects and replicates within host cells, then commandeers the cell’s own machinery to spread to neighboring cells. In line with previous observations (<a href="#bib1">Chen et al., 2017</a>), Rivera-Lugo et al. report that genetically inhibiting respiration causes <i>L. monocytogenes</i> to lyse – burst open and die – within host cells.',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'text' => 'When a bacterium lyses inside a cell, it releases a number of molecules that initiate an antimicrobial response and significantly reduce the bacterium’s ability to cause disease (<a href="#bib6">Sauer et al., 2010</a>). Thus, the tendency for a respiration-deficient mutant to lyse within host cells may inadvertently trigger the host\'s immune response and lead to clearance of the pathogen from the host. Rivera-Lugo et al. showed that restoring NAD<sup>+</sup> regeneration with NOX stopped <i>L. monocytogenes</i> from lysing within infected cells and restored the bacterium’s ability to colonize a mouse. Together these findings imply that respiration-mediated redox balance is crucial for maintaining <i>L. monocytogenes</i> viability during infection (<a href="#fig1">Figure 1</a>).',
                                    'type' => 'paragraph',
                                ],
                                [
                                    'assets' => [
                                        [
                                            'caption' => [
                                                [
                                                    'text' => '(<b>A</b>) <i>L. monocytogenes</i> uses respiration to restore redox balance during growth through acetic acid fermentation by transferring electrons from NADH to an electron acceptor such as oxygen (O<sub>2</sub>). This regenerates NAD<sup>+</sup> to serve as an essential cofactor in the oxidative metabolic reactions that produce ATP. (<b>B</b>) Inhibiting respiration causes an imbalance between NAD<sup>+</sup> and NADH, leading to NADH accumulation and lysis of <i>L. monocytogenes</i> during intracellular growth. This leads to a loss of pathogenesis.',
                                                    'type' => 'paragraph',
                                                ],
                                            ],
                                            'id' => 'fig1',
                                            'image' => [
                                                'alt' => '',
                                                'uri' => 'https://iiif.elifesciences.org/lax:'.$id.'%2Felife-'.$id.'-fig1-v1.tif',
                                                'size' => [
                                                    'width' => 2848,
                                                    'height' => 1207,
                                                ],
                                                'source' => [
                                                    'mediaType' => 'image/jpeg',
                                                    'uri' => 'https://iiif.elifesciences.org/lax:'.$id.'%2Felife-'.$id.'-fig1-v1.tif/full/full/0/default.jpg',
                                                    'filename' => 'elife-'.$id.'-fig1-v1.jpg',
                                                ],
                                            ],
                                            'label' => 'Figure 1',
                                            'title' => 'Effects of inhibiting respiration in <i>L. monocytogenes</i>.',
                                            'type' => 'image',
                                        ],
                                    ],
                                    'type' => 'figure',
                                ],
                                [
                                    'text' => 'The findings of Rivera-Lugo et al. address a long-standing mystery as to why respiration is required for successful <i>L. monocytogenes</i> infection. One outstanding question is how the accumulation of NADH leads to lysis. Understanding the molecular mechanism behind this phenomenon, and determining whether inhibiting respiration externally (with a drug, for example) leads to lysis, could reveal new therapeutic approaches for targeting organisms that employ similar respiro-fermentative metabolic strategies during systemic infection.',
                                    'type' => 'paragraph',
                                ],
                            ],
                            'id' => 's0',
                            'title' => 'Main text',
                            'type' => 'section',
                        ],
                    ],
                    'references' => [
                        [
                            'articleTitle' => 'A genetic screen reveals that synthesis of 1,4-dihydroxy-2-naphthoate (DHNA), but not full-length menaquinone, is required for <i>Listeria monocytogenes</i> cytosolic survival',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Chen, GY',
                                        'preferred' => 'Chen GY',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'McDougal, CE',
                                        'preferred' => 'McDougal CE',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'D’Antonio, MA',
                                        'preferred' => 'D’Antonio MA',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Portman, JL',
                                        'preferred' => 'Portman JL',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Sauer, JD',
                                        'preferred' => 'Sauer JD',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2017',
                            'doi' => '10.1128/mBio.00119-17',
                            'id' => 'bib1',
                            'journal' => 'mBio',
                            'pages' => 'e00119-17',
                            'pmid' => 28325762,
                            'type' => 'journal',
                            'volume' => '8',
                        ],
                        [
                            'articleTitle' => '<i>Listeria monocytogenes</i> has both cytochrome <i>bd</i>-type and cytochrome <i>aa</i><sub>3</sub>-type terminal oxidases, which allow growth at different oxygen levels, and both are important in infection',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Corbett, D',
                                        'preferred' => 'Corbett D',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Goldrick, M',
                                        'preferred' => 'Goldrick M',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Fernandes, VE',
                                        'preferred' => 'Fernandes VE',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Davidge, K',
                                        'preferred' => 'Davidge K',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Poole, RK',
                                        'preferred' => 'Poole RK',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Andrew, PW',
                                        'preferred' => 'Andrew PW',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Cavet, J',
                                        'preferred' => 'Cavet J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Roberts, IS',
                                        'preferred' => 'Roberts IS',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2017',
                            'doi' => '10.1128/IAI.00354-17',
                            'id' => 'bib2',
                            'journal' => 'Infection and Immunity',
                            'pages' => 'e00354-17',
                            'pmid' => 28808161,
                            'type' => 'journal',
                            'volume' => '85',
                        ],
                        [
                            'articleTitle' => 'Extracellular electron transfer powers flavinylated extracellular reductases in Gram-positive bacteria',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Light, SH',
                                        'preferred' => 'Light SH',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Méheust, R',
                                        'preferred' => 'Méheust R',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Ferrell, JL',
                                        'preferred' => 'Ferrell JL',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Cho, J',
                                        'preferred' => 'Cho J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Deng, D',
                                        'preferred' => 'Deng D',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Agostoni, M',
                                        'preferred' => 'Agostoni M',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Iavarone, AT',
                                        'preferred' => 'Iavarone AT',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Banfield, JF',
                                        'preferred' => 'Banfield JF',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'D’Orazio, SEF',
                                        'preferred' => 'D’Orazio SEF',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Portnoy, DA',
                                        'preferred' => 'Portnoy DA',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2019',
                            'doi' => '10.1073/pnas.1915678116',
                            'id' => 'bib3',
                            'journal' => 'PNAS',
                            'pages' => [
                                'first' => '26892',
                                'last' => '26899',
                                'range' => '26892–26899',
                            ],
                            'pmid' => 31818955,
                            'type' => 'journal',
                            'volume' => '116',
                        ],
                        [
                            'articleTitle' => 'Is the glycolytic flux in <i>Lactococcus lactis</i> primarily controlled by the redox charge? Kinetics of NAD(+) and NADH pools determined in vivo by 13C NMR',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Neves, AR',
                                        'preferred' => 'Neves AR',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Ventura, R',
                                        'preferred' => 'Ventura R',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Mansour, N',
                                        'preferred' => 'Mansour N',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Shearman, C',
                                        'preferred' => 'Shearman C',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Gasson, MJ',
                                        'preferred' => 'Gasson MJ',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Maycock, C',
                                        'preferred' => 'Maycock C',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Ramos, A',
                                        'preferred' => 'Ramos A',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Santos, H',
                                        'preferred' => 'Santos H',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2002',
                            'doi' => '10.1074/jbc.M202573200',
                            'id' => 'bib4',
                            'journal' => 'The Journal of Biological Chemistry',
                            'pages' => [
                                'first' => '28088',
                                'last' => '28098',
                                'range' => '28088–28098',
                            ],
                            'pmid' => 12011086,
                            'type' => 'journal',
                            'volume' => '277',
                        ],
                        [
                            'articleTitle' => '<i>Listeria monocytogenes</i> requires cellular respiration for NAD<sup>+</sup> regeneration and pathogenesis',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Rivera-Lugo, R',
                                        'preferred' => 'Rivera-Lugo R',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Deng, D',
                                        'preferred' => 'Deng D',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Anaya-Sanchez, A',
                                        'preferred' => 'Anaya-Sanchez A',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Tejedor-Sanz, S',
                                        'preferred' => 'Tejedor-Sanz S',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Tang, E',
                                        'preferred' => 'Tang E',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Reyes Ruiz, VM',
                                        'preferred' => 'Reyes Ruiz VM',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Smith, HB',
                                        'preferred' => 'Smith HB',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Titov, DV',
                                        'preferred' => 'Titov DV',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Sauer, JD',
                                        'preferred' => 'Sauer JD',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Skaar, EP',
                                        'preferred' => 'Skaar EP',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Ajo-Franklin, CM',
                                        'preferred' => 'Ajo-Franklin CM',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Portnoy, DA',
                                        'preferred' => 'Portnoy DA',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Light, SH',
                                        'preferred' => 'Light SH',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2022',
                            'doi' => '10.7554/eLife.75424',
                            'id' => 'bib5',
                            'journal' => 'eLife',
                            'pages' => 'e75424',
                            'type' => 'journal',
                            'volume' => '11',
                        ],
                        [
                            'articleTitle' => '<i>Listeria monocytogenes</i> triggers AIM2-mediated pyroptosis upon infrequent bacteriolysis in the macrophage cytosol',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Sauer, J-D',
                                        'preferred' => 'Sauer J-D',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Witte, CE',
                                        'preferred' => 'Witte CE',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Zemansky, J',
                                        'preferred' => 'Zemansky J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Hanson, B',
                                        'preferred' => 'Hanson B',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Lauer, P',
                                        'preferred' => 'Lauer P',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Portnoy, DA',
                                        'preferred' => 'Portnoy DA',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2010',
                            'doi' => '10.1016/j.chom.2010.04.004',
                            'id' => 'bib6',
                            'journal' => 'Cell Host & Microbe',
                            'pages' => [
                                'first' => '412',
                                'last' => '419',
                                'range' => '412–419',
                            ],
                            'pmid' => 20417169,
                            'type' => 'journal',
                            'volume' => '7',
                        ],
                        [
                            'articleTitle' => 'Growth, virulence, and immunogenicity of <i>Listeria monocytogenes aro</i> mutants',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Stritzker, J',
                                        'preferred' => 'Stritzker J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Janda, J',
                                        'preferred' => 'Janda J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Schoen, C',
                                        'preferred' => 'Schoen C',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Taupp, M',
                                        'preferred' => 'Taupp M',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Pilgrim, S',
                                        'preferred' => 'Pilgrim S',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Gentschev, I',
                                        'preferred' => 'Gentschev I',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Schreier, P',
                                        'preferred' => 'Schreier P',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Geginat, G',
                                        'preferred' => 'Geginat G',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Goebel, W',
                                        'preferred' => 'Goebel W',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2004',
                            'doi' => '10.1128/IAI.72.10.5622-5629.2004',
                            'id' => 'bib7',
                            'journal' => 'Infection and Immunity',
                            'pages' => [
                                'first' => '5622',
                                'last' => '5629',
                                'range' => '5622–5629',
                            ],
                            'pmid' => 15385459,
                            'type' => 'journal',
                            'volume' => '72',
                        ],
                        [
                            'articleTitle' => 'Complementation of mitochondrial electron transport chain by manipulation of the NAD+/NADH ratio',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Titov, DV',
                                        'preferred' => 'Titov DV',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Cracan, V',
                                        'preferred' => 'Cracan V',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Goodman, RP',
                                        'preferred' => 'Goodman RP',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Peng, J',
                                        'preferred' => 'Peng J',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Grabarek, Z',
                                        'preferred' => 'Grabarek Z',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Mootha, VK',
                                        'preferred' => 'Mootha VK',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '2016',
                            'doi' => '10.1126/science.aad4017',
                            'id' => 'bib8',
                            'journal' => 'Science',
                            'pages' => [
                                'first' => '231',
                                'last' => '235',
                                'range' => '231–235',
                            ],
                            'pmid' => 27124460,
                            'type' => 'journal',
                            'volume' => '352',
                        ],
                        [
                            'articleTitle' => 'Citrate cycle and related metabolism of <i>Listeria monocytogenes</i>',
                            'authors' => [
                                [
                                    'name' => [
                                        'index' => 'Trivett, TL',
                                        'preferred' => 'Trivett TL',
                                    ],
                                    'type' => 'person',
                                ],
                                [
                                    'name' => [
                                        'index' => 'Meyer, EA',
                                        'preferred' => 'Meyer EA',
                                    ],
                                    'type' => 'person',
                                ],
                            ],
                            'date' => '1971',
                            'doi' => '10.1128/jb.107.3.770-779.1971',
                            'id' => 'bib9',
                            'journal' => 'Journal of Bacteriology',
                            'pages' => [
                                'first' => '770',
                                'last' => '779',
                                'range' => '770–779',
                            ],
                            'pmid' => 4999414,
                            'type' => 'journal',
                            'volume' => '107',
                        ],
                    ],
                    'stage' => 'published',
                    'statusDate' => '2022-05-20T00:00:00Z',
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.'/versions',
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
                    'received' => '2022-05-20',
                    'accepted' => '2022-05-20',
                    'versions' => [
                        [
                            'status' => 'vor',
                            'id' => $id,
                            'version' => 1,
                            'type' => $type,
                            'doi' => '10.7554/eLife.'.$id,
                            'authorLine' => 'Lauren C Radlinski, Andreas J Bäumler',
                            'title' => 'To breathe or not to breathe?',
                            'titlePrefix' => 'Respiro-Fermentation',
                            'published' => '2022-05-20T00:00:00Z',
                            'versionDate' => '2022-05-20T00:00:00Z',
                            'volume' => 11,
                            'elocationId' => 'e'.$id,
                            'pdf' => 'https://cdn.elifesciences.org/articles/'.$id.'/elife-'.$id.'-v1.pdf',
                            'subjects' => [
                                [
                                    'id' => 'biochemistry-chemical-biology',
                                    'name' => 'Biochemistry and Chemical Biology',
                                ],
                                [
                                    'id' => 'microbiology-infectious-disease',
                                    'name' => 'Microbiology and Infectious Disease',
                                ],
                            ],
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Radlinski and Bäumler',
                                'statement' => 'This article is distributed under the terms of the <a href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution License</a>, which permits unrestricted use and redistribution provided that the original author and source are credited.',
                            ],
                            'impactStatement' => '<i>Listeria monocytogenes</i> uses respiration to sustain a risky fermentative lifestyle during infection.',
                            'stage' => 'published',
                            'statusDate' => '2022-05-20T00:00:00Z',
                        ],
                    ],
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/'.$id);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $breadcrumb = $crawler->filter('.breadcrumb-item a');
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals($expectedBreadcrumb, $breadcrumb->extract(['_text', 'href']));

        $this->assertSame('Respiro-Fermentation: To breathe or not to breathe?', $crawler->filter('.content-header__title')->text());
        if (empty($expectImpactStatement)) {
            $this->assertEmpty($crawler->filter('.content-header__impact-statement'));
        } else {
            $this->assertSame($expectImpactStatement, $crawler->filter('.content-header__impact-statement')->text());
        }

        if (empty($expectedTabbedNavigation)) {
            $this->assertEmpty($crawler->filter('.tabbed-navigation__tab-label'));
        } else {
            $this->assertSame(
                $expectedTabbedNavigation,
                $crawler->filter('.tabbed-navigation__tab-label a')->extract(['_text', 'href'])
            );
        }

        if (empty($expectedTabbedNavigation)) {
            // Authors appear in main-content-grid and not in content-header.
            $this->assertEmpty($crawler->filter('.content-header .author_list_item'));
            $this->assertEmpty($crawler->filter('.content-header .institution_list_item'));

            $authors = $crawler->filter('.main-content-grid .author_list_item');
            $institutions = $crawler->filter('.main-content-grid .institution_list_item');
        } else {
            // Authors appear in content-header and not in main-content-grid.
            $this->assertEmpty($crawler->filter('.main-content-grid .author_list_item'));
            $this->assertEmpty($crawler->filter('.main-content-grid .institution_list_item'));

            $authors = $crawler->filter('.content-header .author_list_item');
            $institutions = $crawler->filter('.content-header .institution_list_item');
        }

        $this->assertCount(2, $authors);
        $this->assertSame('Lauren C Radlinski', $authors->eq(0)->filter('a')->text());
        $this->assertSame('Andreas J Bäumler', $authors->eq(1)->filter('a')->text());

        $this->assertCount(1, $institutions);
        $this->assertSame('Department of Medical Microbiology and Immunology, School of Medicine, University of California, Davis, United States;', $this->crawlerText($institutions->eq(0)));

        $sections = $crawler->filter('.main-content-grid > .article-section');
        if (empty($expectedJumpMenu)) {
            // Abstract does not appear in main-content-grid but populates the impact statement property.
            $this->assertNotContains('Abstract', $crawler->filter('.main-content-grid')->text());
            // The Main text heading does not appear for insights and editorials.
            $this->assertNotContains('Main text', $crawler->filter('.main-content-grid')->text());
            $this->assertCount(4, $sections);
            $this->assertEmpty($sections->eq(0)->filter('h2'));
            $references = $sections->eq(1);
            $articleAndAuthorInfo = $sections->eq(2);
            $downloadLinks = $sections->eq(3);
            $categoriesAndTags = $crawler->filter('.main-content-grid > section')->eq(4);
        } else {
            // Verify that Abstract and Main text sections and headings are present in main-content-grid for feature articles.
            $this->assertSame('Abstract', $sections->eq(0)->filter('h2')->text());
            $this->assertSame('Main text', $sections->eq(1)->filter('h2')->text());
            $references = $sections->eq(2);
            $articleAndAuthorInfo = $sections->eq(3);
            $downloadLinks = $sections->eq(4);
            $categoriesAndTags = $crawler->filter('.main-content-grid > section')->eq(5);
        }

        $this->assertSame('References', $references->filter('h2')->text());
        $this->assertSame('Article and author information', $articleAndAuthorInfo->filter('h2')->text());

        $this->assertSame('Download links', $downloadLinks->filter('h2')->text());
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
        $this->assertSame('Lauren C Radlinski Andreas J Bäumler (2022) Respiro-Fermentation: To breathe or not to breathe? eLife 11:e'.$id.'. https://doi.org/10.7554/eLife.'.$id, $this->crawlerText($citeThisArticle->filter('.reference')));
        $citeThisArticleLinks = $citeThisArticle->filter('.article-download-links-list__item');
        $this->assertCount(2, $citeThisArticleLinks);
        $this->assertSame('Download BibTeX', $this->crawlerText($citeThisArticleLinks->eq(0)));
        $this->assertSame('Download .RIS', $this->crawlerText($citeThisArticleLinks->eq(1)));

        $this->assertSame('Categories and tags', $categoriesAndTags->filter('h4')->text());
    }

    public function contentAsideProvider() : Traversable
    {
        yield 'with content-aside' => [
            'research-article',
            true,
        ];

        foreach ([
            'editorial',
            'feature',
            'insight',
        ] as $type) {
            yield 'without content-aside '.$type => [
                $type,
                false,
            ];
        }
    }

    /**
     * @test
     * @dataProvider contentAsideProvider
     */
    public function it_may_have_content_aside(
        string $type,
        bool $hasContentAside
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 2,
                    'type' => $type,
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article 1 title',
                    'published' => '2009-12-31T00:00:00Z',
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
                    'received' => '2009-12-30',
                    'accepted' => '2009-12-31',
                    'sentForReview' => '2009-12-29',
                    'versions' => [
                        [
                            'status' => 'preprint',
                            'description' => 'Description of preprint',
                            'uri' => 'http://example.preprint.com',
                            'date' => '2009-12-28T00:00:00Z',
                        ],
                        [
                            'status' => 'poa',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => $type,
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
                        [
                            'status' => 'vor',
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 2,
                            'type' => $type,
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article 1 title',
                            'published' => '2010-01-01T00:00:00Z',
                            'versionDate' => '2010-01-02T00:00:00Z',
                            'statusDate' => '2010-01-02T00:00:00Z',
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

        if ($hasContentAside) {
            $this->assertSame('Version of Record', $crawler->filter('.content-aside .status-title')->text());
            $this->assertSame('Accepted for publication after peer review and revision.', $crawler->filter('.content-aside .status-description')->text());
            $this->assertSame('Download',
                $this->crawlerText($crawler->filter('.content-aside .button-collection .button-collection__item')->eq(0)));
            $this->assertSame('Cite',
                $this->crawlerText($crawler->filter('.content-aside .button-collection .button-collection__item')->eq(1)));
            $this->assertSame('Share',
                $this->crawlerText($crawler->filter('.content-aside .button-collection .button-collection__item')->eq(2)));
            $this->assertSame('Comment Open annotations (there are currently 0 annotations on this page).',
                $this->crawlerText($crawler->filter('.content-aside .button-collection .button-collection__item')->eq(3)));
            $this->assertCount(12, $crawler->filter('.content-aside .definition-list--timeline')->children());

            foreach ([
                         'Version of Record published',
                         'January 2, 2010 (This version)',
                         'Accepted Manuscript published',
                         'January 1, 2010 (Go to version)',
                         'Accepted',
                         'December 31, 2009',
                         'Received',
                         'December 30, 2009',
                         'Sent for peer review',
                         'December 29, 2009',
                         'Preprint posted',
                         'December 28, 2009 (Go to version)',
                     ] as $k => $expectedTimeline) {
                $this->assertSame(
                    $expectedTimeline,
                    $crawler->filter('.content-aside .definition-list--timeline')->children()->eq($k)->text()
                );
            }
        } else {
            $this->assertCount(0, $crawler->filter('.content-aside'));
        }
    }

    public function contentAsideStatusProvider() : Traversable
    {
        yield 'poa' => [
            'research-article',
            'poa',
            'e',
            'Author Accepted Manuscript',
            'PDF only version. The full online version will follow soon.',
        ];

        foreach ([
            'research-article',
            'research-advance',
            'review-article',
            'scientific-correspondence',
            'short-report',
            'tools-resources',
        ] as $type) {
            yield 'vor '.$type => [
                $type,
                'vor',
                'e',
                'Version of Record',
                'Accepted for publication after peer review and revision.',
            ];
        }

        yield 'vor prc' => [
            'research-article',
            'vor',
            'RP',
            'Version of Record',
            'The authors declare this version of their article to be the Version of Record.',
        ];

        foreach ([
            'correction',
            'retraction',
            'registered-report',
            'replication-study',
            'research-communication',
        ] as $type) {
            yield 'no status '.$type => [
                $type,
                'vor',
                'e',
                null,
                null,
            ];
        }
    }

    /**
     * @test
     * @dataProvider contentAsideStatusProvider
     */
    public function it_may_have_a_content_aside_status(
        string $type,
        string $status = 'vor',
        string $elocationIdPrefix = 'e',
        string $expectedTitle = null,
        string $expectedDescription = null
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-'.$status.'+json; version='.('poa' === $status ? 3 : 7)],
                json_encode([
                    'status' => $status,
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 1,
                    'type' => $type,
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article 1 title',
                    'published' => '2009-12-31T00:00:00Z',
                    'versionDate' => '2010-01-01T00:00:00Z',
                    'statusDate' => '2010-01-01T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => $elocationIdPrefix.'00001',
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
                            'status' => $status,
                            'stage' => 'published',
                            'id' => '00001',
                            'version' => 1,
                            'type' => $type,
                            'doi' => '10.7554/eLife.00001',
                            'title' => 'Article 1 title',
                            'published' => '2009-12-31T00:00:00Z',
                            'versionDate' => '2010-01-01T00:00:00Z',
                            'statusDate' => '2010-01-01T00:00:00Z',
                            'volume' => 1,
                            'elocationId' => $elocationIdPrefix.'00001',
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

        if (null === $expectedTitle) {
            $this->assertCount(0, $crawler->filter('.content-aside .status-title'));
            $this->assertCount(0, $crawler->filter('.content-aside .status-description'));
            $this->assertCount(0, $crawler->filter('.content-aside .definition-list--timeline'));
        } else {
            $this->assertSame($expectedTitle, $crawler->filter('.content-aside .status-title')->text());
            $this->assertSame($expectedDescription, $crawler->filter('.content-aside .status-description')->text());
            $this->assertGreaterThan(0, $crawler->filter('.content-aside .definition-list--timeline')->count());
        }

        // status link only appears on new VORs
        if ('RP' === $elocationIdPrefix) {
            $this->assertSame('About eLife\'s process', $crawler->filter('.content-aside .status-link')->text());
        } else {
            $this->assertSame(0, $crawler->filter('.content-aside .status-link')->count());
        }
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
                    'Full text',
                    '/articles/00001#content',
                ],
            ],
            $crawler->filter('.tabbed-navigation__tab-label a')->extract(['_text', 'href'])
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
        $this->assertSame('This article has been corrected. Read the correction notice.', trim($crawler->filter('.info-bar--correction')->text()));
        $this->assertSame('This article has been retracted. Read the retraction notice.', trim($crawler->filter('.info-bar--attention')->text()));
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

    /**
     * @test
     */
    public function it_displays_vor_prc_article()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
                    'elocationId' => 'RP00001',
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
                    'authorResponse' => [
                        'content' => [
                            [
                                'text' => '<i>1) The reviewers are surprised to not see an in-depth comparison of</i> H. naledi <i>to</i> H. floresiensis<i>, especially where combinations of small teeth and small brains are concerned. It should be easy, e.g., to add the published</i> H. floresiensis <i>measurements to</i> <a href="#fig7"><i>Figure 7</i></a><i>. The authors allude to material attributed to</i> ‘Homo gautengensis’ <i>and perhaps a short discussion or reiteration of their views about the validity of that species is needed</i>.',
                                'type' => 'paragraph',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09562.031',
                        'id' => 'SA2',
                    ],
                    'elifeAssessment' => [
                        'title' => 'eLife assessment',
                        'id' => 'sa0',
                        'doi' => '10.7554/eLife.09562.sa00',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Collagen is a major component of extracellular matrix. The authors have identified a high-affinity inhibitory collagen receptor LAIR-1 and a soluble decoy receptor LAIR-2 (with even higher binding affinity to collagen), which can be therapeutically targeted to block tumor progression. Dr Meyaard and colleagues have also generated a dimeric LAIR-2 human IgG1 Fc fusion protein NC410 for therapeutic use. With humanized mouse models engrafted with functional human immune systems (PBMC), they have explored the anti-cancer efficacy of NC410 and revealed its impact on modulating immune responses. Furthermore, they extended this study to identify biomarkers of predictive value for NC410-based anti-cancer therapy.'
                            ],
                        ],
                        'scietyUri' => 'https://sciety.org/articles/activity/10.1101/2020.11.21.391326'
                    ],
                    'publicReviews' => [
                        [
                            'title' => 'Reviewer #1 (public review)',
                            'id' => 'SA21',
                            'doi' => '10.7554/eLife.09562.230',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.',
                                ],
                                [
                                    'type' => 'box',
                                    'title' => 'Box 2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Reviewer #2 (public review)',
                            'id' => 'SA22',
                            'doi' => '10.7554/eLife.09562.330',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.'
                                ],
                                [
                                    'type' => 'box',
                                    'title' => 'Box 2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.'
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.'
                                ],
                            ],
                        ],
                    ],
                    'recommendationsForAuthors' => [
                        'title' => 'Recommendations for authors',
                        'id' => 'SA11',
                        'doi' => '10.7554/eLife.09562.130',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.'
                            ],
                            [
                                'type' => 'box',
                                'title' => 'Box 2',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.'
                                    ],
                                ],
                            ],
                            [
                                'type' => 'paragraph',
                                'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.'
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
                    'received' => '2023-12-10',
                    'accepted' => '2023-12-10',
                    'sentForReview' => '2023-03-15',
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
                            'elocationId' => 'RP00001',
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
        $this->assertSame('eLife assessment', trim($crawler->filter('.jump-menu__item')->eq(0)->text()));
        $this->assertSame('Peer review', trim($crawler->filter('.jump-menu__item')->eq(2)->text()));
        $this->assertSame('eLife assessment',
            $crawler->filter('.main-content-grid > section:nth-of-type(1) > header > h2')->text());
        $this->assertSame('Peer review',
            $crawler->filter('.main-content-grid > section:nth-of-type(3) > header > h2')->text());
        $publicReviews = $crawler->filter('.main-content-grid > section:nth-of-type(3) > .article-section__body > section');
        $this->assertCount(3, $publicReviews);

        foreach ([
            'https://doi.org/10.7554/eLife.09562.230',
            'https://doi.org/10.7554/eLife.09562.330',
            'https://doi.org/10.7554/eLife.09562.130',
        ] as $k => $expectedDoi) {
            $this->assertSame(
                $expectedDoi,
                trim($publicReviews->eq($k)->filter('.doi__link')->text())
            );
        }

        $this->assertSame('Recommendations for authors',
            $crawler->filter('.main-content-grid > section:nth-of-type(3) > div > section:nth-of-type(3) > header > h3')->text());
    }

    public function prcVorHistoryProvider()
    {
        yield 'none' => [
            [],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
            ],
        ];
        yield 'preprint only' => [
            [
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a preprint.',
                    'uri' => 'https://doi.org/10.1101/2021.11.09.467796',
                    'date' => '2023-02-15T00:00:00Z',
                ],
            ],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
                'Preprint posted',
                'February 15, 2023 (Go to version)',
            ],
        ];
        yield 'reviewed preprint only' => [
            [
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a reviewed preprint.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.1',
                    'date' => '2023-02-16T00:00:00Z',
                ],
            ],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
                'Reviewed preprint posted',
                'February 16, 2023 (Go to version)',
            ],
        ];
        yield 'revised preprint' => [
            [
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a reviewed preprint.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.1',
                    'date' => '2023-02-16T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'The reviewed preprint was revised.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.2',
                    'date' => '2023-02-17T00:00:00Z',
                ],
            ],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
                'Reviewed preprint version 2',
                'February 17, 2023 (Go to version)',
                'Reviewed preprint version 1',
                'February 16, 2023 (Go to version)',
            ],
        ];
        yield 'revised preprints and preprint' => [
            [
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a preprint.',
                    'uri' => 'https://doi.org/10.1101/2021.11.09.467796',
                    'date' => '2023-02-15T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a reviewed preprint.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.1',
                    'date' => '2023-02-16T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'The reviewed preprint was revised.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.2',
                    'date' => '2023-02-17T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'The reviewed preprint was revised.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.3',
                    'date' => '2023-02-18T00:00:00Z',
                ],
            ],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
                'Reviewed preprint version 3',
                'February 18, 2023 (Go to version)',
                'Reviewed preprint version 2',
                'February 17, 2023 (Go to version)',
                'Reviewed preprint version 1',
                'February 16, 2023 (Go to version)',
                'Preprint posted',
                'February 15, 2023 (Go to version)',
            ],
        ];
        yield 'revised preprints and preprint disordered' => [
            [
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a reviewed preprint.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.1',
                    'date' => '2023-02-16T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'This manuscript was published as a preprint.',
                    'uri' => 'https://doi.org/10.1101/2021.11.09.467796',
                    'date' => '2023-02-15T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'The reviewed preprint was revised.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.3',
                    'date' => '2023-02-18T00:00:00Z',
                ],
                [
                    'status' => 'preprint',
                    'description' => 'The reviewed preprint was revised.',
                    'uri' => 'https://doi.org/10.7554/eLife.00001.2',
                    'date' => '2023-02-17T00:00:00Z',
                ],
            ],
            [
                'Version of Record published',
                'May 3, 2023 (This version)',
                'Reviewed preprint version 3',
                'February 18, 2023 (Go to version)',
                'Reviewed preprint version 2',
                'February 17, 2023 (Go to version)',
                'Reviewed preprint version 1',
                'February 16, 2023 (Go to version)',
                'Preprint posted',
                'February 15, 2023 (Go to version)',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider prcVorHistoryProvider
     */
    public function it_displays_versions_in_timeline_for_prc_vor(
        array $preprints,
        array $expectedTimeline
    )
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00001',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.00001',
                    'title' => 'Article 1 title',
                    'published' => '2023-05-03T00:00:00Z',
                    'versionDate' => '2023-05-03T00:00:00Z',
                    'statusDate' => '2023-05-03T00:00:00Z',
                    'volume' => 1,
                    'elocationId' => 'RP00001',
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
                    'authorResponse' => [
                        'content' => [
                            [
                                'text' => '<i>1) The reviewers are surprised to not see an in-depth comparison of</i> H. naledi <i>to</i> H. floresiensis<i>, especially where combinations of small teeth and small brains are concerned. It should be easy, e.g., to add the published</i> H. floresiensis <i>measurements to</i> <a href="#fig7"><i>Figure 7</i></a><i>. The authors allude to material attributed to</i> ‘Homo gautengensis’ <i>and perhaps a short discussion or reiteration of their views about the validity of that species is needed</i>.',
                                'type' => 'paragraph',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09562.031',
                        'id' => 'SA2',
                    ],
                    'elifeAssessment' => [
                        'title' => 'eLife assessment',
                        'id' => 'sa0',
                        'doi' => '10.7554/eLife.09562.sa00',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Collagen is a major component of extracellular matrix. The authors have identified a high-affinity inhibitory collagen receptor LAIR-1 and a soluble decoy receptor LAIR-2 (with even higher binding affinity to collagen), which can be therapeutically targeted to block tumor progression. Dr Meyaard and colleagues have also generated a dimeric LAIR-2 human IgG1 Fc fusion protein NC410 for therapeutic use. With humanized mouse models engrafted with functional human immune systems (PBMC), they have explored the anti-cancer efficacy of NC410 and revealed its impact on modulating immune responses. Furthermore, they extended this study to identify biomarkers of predictive value for NC410-based anti-cancer therapy.',
                            ],
                        ],
                        'scietyUri' => 'https://sciety.org/articles/activity/10.1101/2020.11.21.391326',
                    ],
                    'publicReviews' => [
                        [
                            'title' => 'Reviewer #1 (public review)',
                            'id' => 'SA21',
                            'doi' => '10.7554/eLife.09562.230',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.',
                                ],
                                [
                                    'type' => 'box',
                                    'title' => 'Box 2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Reviewer #2 (public review)',
                            'id' => 'SA22',
                            'doi' => '10.7554/eLife.09562.330',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.',
                                ],
                                [
                                    'type' => 'box',
                                    'title' => 'Box 2',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'paragraph',
                                    'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.',
                                ],
                            ],
                        ],
                    ],
                    'recommendationsForAuthors' => [
                        'title' => 'Recommendations for authors',
                        'id' => 'SA11',
                        'doi' => '10.7554/eLife.09562.130',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Thank you for submitting your work entitled “A new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa” for peer review at <i>eLife</i>.',
                            ],
                            [
                                'type' => 'box',
                                'title' => 'Box 2',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Your submission has been favorably evaluated by Ian Baldwin (Senior editor), two guest Reviewing editors (Johannes Krause and Nicholas Conard), and two peer reviewers. One of the two peer reviewers, Chris Stringer, has agreed to share his identity, and Johannes Krause has drafted this decision to help you prepare a revised submission.',
                                    ],
                                ],
                            ],
                            [
                                'type' => 'paragraph',
                                'text' => 'The authors describe a large collection of recently discovered hominin fossils from the Dinaledi Chamber in the Rising Star cave system in South Africa. Based on their initial assessment they argue that the fossil remains derive from a single homogenous hominin group and present a new taxon that they call <i>Homo naledi</i>.',
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
                    'versions' => array_merge(
                        $preprints,
                        [
                            [
                                'status' => 'vor',
                                'stage' => 'published',
                                'id' => '00001',
                                'version' => 1,
                                'type' => 'research-article',
                                'doi' => '10.7554/eLife.00001',
                                'title' => 'Article 1 title',
                                'published' => '2023-05-03T00:00:00Z',
                                'versionDate' => '2023-05-03T00:00:00Z',
                                'statusDate' => '2023-05-03T00:00:00Z',
                                'volume' => 1,
                                'elocationId' => 'RP00001',
                                'copyright' => [
                                    'license' => 'CC-BY-4.0',
                                    'holder' => 'Bar',
                                    'statement' => 'Copyright statement.',
                                ],
                                'authorLine' => 'Foo Bar',
                            ]
                        ]
                    ),
                ])
            )
        );

        $crawler = $client->request('GET', '/articles/00001');
        $this->assertCount(count($expectedTimeline), $crawler->filter('.content-aside .definition-list--timeline')->children());
        foreach ($expectedTimeline as $k => $expectedTimelineItem) {
            $this->assertSame(
                $expectedTimelineItem,
                $crawler->filter('.content-aside .definition-list--timeline')->children()->eq($k)->text()
            );
        }
    }

    protected function getUrl($articleId = '00001') : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/{$articleId}",
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
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
