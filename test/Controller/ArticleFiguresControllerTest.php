<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ArticleFiguresControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_article_figures_page()
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

        $figureTypes = $crawler->filter('main > .wrapper > div > div > section');
        $this->assertCount(5, $figureTypes);

        $figures = $figureTypes->eq(0)->filter('.asset-viewer-inline');
        $this->assertSame('Image 1 label', trim($figures->eq(0)->filter('.asset-viewer-inline__header_text')->text()));
        $this->assertSame('Image 2 label', trim($figures->eq(1)->filter('.asset-viewer-inline__header_text')->text()));
        $this->assertSame('Image 2 source data 1 label', trim($figures->eq(1)->filter('.additional-assets__list .caption-text__heading')->text()));
        $this->assertSame('Image 2 supplement 1 label', trim($figures->eq(2)->filter('.asset-viewer-inline__header_text')->text()));
        $this->assertSame('Image 2 supplement 1 source data 1 label', trim($figures->eq(2)->filter('.additional-assets__list .caption-text__heading')->text()));

        $videos = $figureTypes->eq(1)->filter('.asset-viewer-inline');
        $this->assertSame('Video 1 label', trim($videos->eq(0)->filter('.asset-viewer-inline__header_text')->text()));

        $tables = $figureTypes->eq(2)->filter('.asset-viewer-inline');
        $this->assertSame('Table 1 label', trim($tables->eq(0)->filter('.asset-viewer-inline__header_text')->text()));

        $dataSets = $figureTypes->eq(3)->filter('.article-section__body')->children();
        $this->assertSame('The following data sets were generated', trim($dataSets->eq(0)->text()));
        $this->assertSame('Data set 1', trim($dataSets->eq(1)->filter('.reference__title')->text()));
        $this->assertSame('The following previously published data sets were used', trim($dataSets->eq(2)->text()));
        $this->assertSame('Data set 2', trim($dataSets->eq(3)->filter('.reference__title')->text()));

        $additionalFiles = $figureTypes->eq(4)->filter('.caption-text__heading');
        $this->assertSame('Additional file 1 label', trim($additionalFiles->eq(0)->text()));

        $this->assertSame(
            [
                'Figures',
                'Videos',
                'Tables',
                'Data sets',
                'Additional files',
            ],
            array_map('trim', $crawler->filter('.view-selector__jump_link_item')->extract('_text'))
        );
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

        $client->request('GET', '/content/1/e00001/figures');

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

        $client->request('GET', '/content/1/e00001/figures');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
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
                    'body' => [
                        [
                            'type' => 'section',
                            'id' => 's-1',
                            'title' => 'Introduction',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'id' => 'image1',
                                    'label' => 'Image 1 label',
                                    'title' => 'Image 1 title',
                                    'alt' => '',
                                    'uri' => 'https://placehold.it/900x450',
                                ],
                                [
                                    'type' => 'image',
                                    'alt' => '',
                                    'uri' => 'https://placehold.it/900x450',
                                ],
                                [
                                    'type' => 'section',
                                    'title' => 'Sub-section',
                                    'content' => [
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
                                            'image' => 'https://placehold.it/900x450',
                                            'width' => 900,
                                            'height' => 450,
                                        ],
                                        [
                                            'type' => 'image',
                                            'id' => 'image2',
                                            'label' => 'Image 2 label',
                                            'title' => 'Image 2 title',
                                            'alt' => '',
                                            'uri' => 'https://placehold.it/900x450',
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
                                            'supplements' => [
                                                [
                                                    'id' => 'image2s1',
                                                    'label' => 'Image 2 supplement 1 label',
                                                    'title' => 'Image 2 supplement 1 title',
                                                    'alt' => '',
                                                    'uri' => 'https://placehold.it/900x450',
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
                    'dataSets' => [
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

        return '/content/1/e00001/figures';
    }
}
