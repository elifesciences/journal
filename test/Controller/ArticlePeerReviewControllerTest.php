<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ArticlePeerReviewControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_article_peer_review_page_for_a_vor()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Foo Bar', trim($crawler->filter('.author_list')->text(), " \n,"));
        $this->assertEmpty($crawler->filter('.institution_list'));
        $this->assertSame('tabbed-navigation__tab-label tabbed-navigation__tab-label--active',
            $crawler->filter('ul.tabbed-navigation__tabs li')->eq(2)->attr('class'));
        $this->assertEmpty($crawler->filter('.contextual-data__list'));

        $articleInfo = $crawler->filter('.main-content-grid');
        $editors = $articleInfo->filter('section#editors');
        $this->assertSame('Editors', $editors->filter('h2.article-section__header_text')->text());
        $this->assertSame('Senior Editor', $editors->filter('header > h3')->text());

        $this->assertSame('Reviewer #1 (public review)',
            $crawler->filter('.main-content-grid > section:nth-of-type(2) header > h2')->text());
        $this->assertSame('Reviewer #2 (public review)',
            $crawler->filter('.main-content-grid > section:nth-of-type(3) header > h2')->text());
         $this->assertSame('Decision letter',
            $crawler->filter('.main-content-grid > section:nth-of-type(4) header > h2')->text());
       $this->assertSame('Author response',
            $crawler->filter('.main-content-grid > section:nth-of-type(5) header > h2')->text());

         $this->assertSame('Author response text',
             $crawler->filter('.main-content-grid > section:nth-of-type(9) > div > p')->text());


//
//         $this->assertSame('Reviewer 1',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(0)->text());
//         $this->assertSame('Reviewing Editor 1',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(1)->text());
//         $this->assertSame('Senior Editor 1',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(2)->text());
//         $this->assertSame('Reviewer 2',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .profile-snippet__name')->eq(3)->text());
//         $this->assertSame('Decision letter description',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div .decision-letter-header__main_text > p')->text());
//         $this->assertSame('Decision letter text',
//             $crawler->filter('.main-content-grid > section:nth-of-type(8) > div > p')->text());
//         $this->assertSame('Author response', $crawler->filter('#author-response-id h2')->text());
//         $this->assertSame('Author response',
//             $crawler->filter('.main-content-grid > section:nth-of-type(9) > header > h2')->text());
//         $this->assertSame('Author response text',
//             $crawler->filter('.main-content-grid > section:nth-of-type(9) > div > p')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Figures and data in Article title | eLife', $crawler->filter('title')->text());
        $this->assertSame('/articles/00001/figures', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/articles/00001/figures', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Figures and data in Article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
        $this->assertSame('© 2010 Bar. Copyright statement', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
    }

    /**
     * @test
     */
    public function it_displays_an_article_figures_page_for_a_poa()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Article title', $crawler->filter('.content-header__title')->text());
        $this->assertSame('Foo Bar', trim($crawler->filter('.author_list')->text(), " \n,"));
        $this->assertEmpty($crawler->filter('.institution_list'));

        $this->assertSame('1 additional file', trim($crawler->filter('.message-bar')->text()));

        $figureTypes = $crawler->filter('.main-content-grid > section');
        $this->assertCount(2, $figureTypes);

        $this->assertSame('Additional files', $figureTypes->eq(0)->filter('.article-section__header_text')->text());
        $additionalFiles = $figureTypes->eq(0)->filter('.caption-text__heading');
        $this->assertSame('Additional file 1 label', trim($additionalFiles->eq(0)->text()));

        $this->assertSame('Download links', $figureTypes->eq(1)->filter('.article-section__header_text')->text());

        $this->assertEmpty($crawler->filter('.view-selector__jump_link_item'));
    }

    /**
     * @test
     */
    public function it_has_metadata_for_previous_versions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getPreviousVersionUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Figures and data in Article title | eLife', $crawler->filter('title')->text());
        $this->assertSame('/articles/00001/figures', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/articles/00001/figures', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Figures and data in Article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
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
        $this->assertSame('© 2010 Foo Bar. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
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

        $client->request('GET', '/articles/00001/figures');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_has_no_figures()
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

        $client->request('GET', '/articles/00001/figures');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_display_an_article_figures_page_for_a_vor_with_only_a_data_availability_statement()
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
                                'text' => 'Data availability',
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

        $crawler = $client->request('GET', '/articles/00001/figures');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_redirects_an_insight_or_editorial_article_with_figures_to_the_article_page()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00002',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00002',
                    'version' => 1,
                    'type' => 'insight',
                    'doi' => '10.7554/eLife.00002',
                    'title' => 'Insight 4 title',
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
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'section',
                                'title' => 'Introduction',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Abstract 00002.',
                                    ],
                                ],
                            ],
                        ],
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
                'http://api.elifesciences.org/articles/00002/versions',
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
                            'id' => '00002',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00002',
                            'title' => 'Insight 4 title',
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
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        $client->followRedirects(false);

        // Expect a redirect when the insight page has figures, 404 for insight pages without figures
        // (See `::it_displays_a_404_if_the_an_insight_or_editorial_content_has_no_figures()`).
        $client->request('GET', '/articles/00002/figures');
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect());
        $this->assertSameUri('/articles/00002', $response->headers->get('Location'));
    }


    /**
     * @test
     */
    public function it_displays_a_404_if_the_an_insight_or_editorial_content_has_no_figures()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00002',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=7']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=7'],
                json_encode([
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '00002',
                    'version' => 1,
                    'type' => 'insight',
                    'doi' => '10.7554/eLife.00002',
                    'title' => 'Insight 4 title',
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
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'section',
                                'title' => 'Introduction',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Abstract 00002.',
                                    ],
                                ],
                            ],
                        ],
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
                'http://api.elifesciences.org/articles/00002/versions',
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
                            'id' => '00002',
                            'version' => 1,
                            'type' => 'insight',
                            'doi' => '10.7554/eLife.00002',
                            'title' => 'Insight 4 title',
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
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        $client->request('GET', '/articles/00002/figures');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
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
                        'statement' => 'Copyright statement',
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
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
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
                                    ],
                                ],
                                [
                                    'type' => 'image',
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
                                    'type' => 'section',
                                    'title' => 'Sub-section',
                                    'content' => [
                                        [
                                            'type' => 'figure',
                                            'assets' => [
                                                [
                                                    'type' => 'video',
                                                    'id' => 'video1',
                                                    'label' => 'Video 1 label',
                                                    'title' => 'Video 1 table',
                                                    'sources' => [
                                                        [
                                                            'mediaType' => 'video/mp4',
                                                            'uri' => 'https://placehold.it/900x450',
                                                        ],
                                                    ],
                                                    'placeholder' => [
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
                                                    'width' => 900,
                                                    'height' => 450,
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'figure',
                                            'assets' => [
                                                [
                                                    'type' => 'image',
                                                    'id' => 'image2',
                                                    'label' => 'Image 2 label',
                                                    'title' => 'Image 2 title',
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
                                                    'sourceData' => [
                                                        [
                                                            'id' => 'image2-sd1',
                                                            'label' => 'Image 2 source data 1 label',
                                                            'title' => 'Image 2 source data 1 title',
                                                            'mediaType' => 'image/jpeg',
                                                            'uri' => 'https://placehold.it/900x450',
                                                            'filename' => 'image.jpg',
                                                        ],
                                                    ],
                                                ],
                                                [
                                                    'type' => 'image',
                                                    'id' => 'image2s1',
                                                    'label' => 'Image 2 supplement 1 label',
                                                    'title' => 'Image 2 supplement 1 title',
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
                                                    'sourceData' => [
                                                        [
                                                            'id' => 'image2s1-sd1',
                                                            'label' => 'Image 2 supplement 1 source data 1 label',
                                                            'title' => 'Image 2 supplement 1 source data 1 title',
                                                            'mediaType' => 'image/jpeg',
                                                            'uri' => 'https://placehold.it/900x450',
                                                            'filename' => 'image.jpg',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'figure',
                                    'assets' => [
                                        [
                                            'type' => 'table',
                                            'doi' => '10.7554/eLife.09560.013',
                                            'id' => 'table1',
                                            'label' => 'Table 1 label',
                                            'title' => 'Table 1 title',
                                            'tables' => [
                                                '<table><tbody><tr><td>Table</td></tr></tbody></table>',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'appendices' => [
                        [
                            'id' => 'app',
                            'title' => 'Appendix',
                            'content' => [
                                [
                                    'type' => 'figure',
                                    'assets' => [
                                        [
                                            'type' => 'image',
                                            'id' => 'appimage',
                                            'label' => 'Appendix image label',
                                            'title' => 'Appendix image title',
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
                    'decisionLetter' => [
                        'doi' => '10.7554/eLife.00001.001',
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
                        'doi' => '10.7554/eLife.00001.002',
                        'content' => [
                            [
                                'type' => 'figure',
                                'assets' => [
                                    [
                                        'type' => 'image',
                                        'id' => 'arimage',
                                        'label' => 'Author response image label',
                                        'title' => 'Author response image title',
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
                    'dataSets' => [
                        'availability' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Data availability',
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
                    'additionalFiles' => [
                        [
                            'id' => 'file1',
                            'label' => 'Additional file 1 label',
                            'title' => 'Additional file 1 title',
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://placehold.it/900x450',
                            'filename' => 'image.jpg',
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
                                'statement' => 'Copyright statement',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        return '/articles/00001/peer-reviews';
    }

    private function getPreviousVersionUrl()
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
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Foo Bar',
                        'statement' => 'Copyright statement.',
                    ],
                    'authorLine' => 'Foo Bar',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Foo Bar',
                                'index' => 'Foo Bar',
                            ],
                        ],
                    ],
                    'additionalFiles' => [
                        [
                            'id' => 'file1',
                            'label' => 'Additional file 1 label',
                            'title' => 'Additional file 1 title',
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://placehold.it/900x450',
                            'filename' => 'image.jpg',
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
                                'holder' => 'Foo Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
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
                                'holder' => 'Foo Bar',
                                'statement' => 'Copyright statement.',
                            ],
                            'authorLine' => 'Foo Bar',
                        ],
                    ],
                ])
            )
        );

        return '/articles/00001v1/figures';
    }
}
