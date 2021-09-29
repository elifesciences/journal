<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\Providers;
use test\eLife\Journal\WebTestCase;
use Traversable;

final class DoiControllerTest extends WebTestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_redirects_article_dois()
    {
        $client = static::createClient();

        $this->mockArticleRequest();

        $client->request('GET', '/lookup/doi/10.7554/eLife.00001');

        $this->assertSame(303, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/articles/00001'));
        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $client->getResponse()->getVary());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_found()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $client->request('GET', '/lookup/doi/10.7554/eLife.00001');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @dataProvider subDoiProvider
     */
    public function it_redirects_article_sub_dois(string $doi, string $expected)
    {
        $client = static::createClient();

        $this->mockArticleRequest();

        $client->request('GET', "/lookup/doi/$doi");

        $this->assertSame(303, $client->getResponse()->getStatusCode());
        $this->assertSame($expected, $client->getResponse()->headers->get('Location'));
        $this->assertSame('max-age=300, public, stale-if-error=86400, stale-while-revalidate=300', $client->getResponse()->headers->get('Cache-Control'));
        $this->assertSame(['Cookie'], $client->getResponse()->getVary());
    }

    public function subDoiProvider() : Traversable
    {
        return $this->arrayProvider([
            '10.7554/eLife.00001.001' => '/articles/00001#abstract',
            '10.7554/eLife.00001.002' => '/articles/00001#image1',
            '10.7554/eLife.00001.003' => '/articles/00001#image1-sd1',
            '10.7554/eLife.00001.004' => '/articles/00001/figures#image1s1',
            '10.7554/eLife.00001.005' => '/articles/00001/figures#image1s1-sd1',
        ]);
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_sub_doi_is_not_found()
    {
        $client = static::createClient();

        $this->mockApiResponse(
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

        $client->request('GET', '/lookup/doi/10.7554/eLife.00001.foo');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    private function mockArticleRequest()
    {
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
                    'abstract' => [
                        'doi' => '10.7554/eLife.00001.001',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'abstract',
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
                                    'text' => 'content',
                                ],
                                [
                                    'type' => 'figure',
                                    'assets' => [
                                        [
                                            'type' => 'image',
                                            'id' => 'image1',
                                            'doi' => '10.7554/eLife.00001.002',
                                            'label' => 'Image 1',
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
                                                    'id' => 'image1-sd1',
                                                    'doi' => '10.7554/eLife.00001.003',
                                                    'label' => 'Image 1 source data 1',
                                                    'title' => 'Image 1 source data 1',
                                                    'mediaType' => 'image/jpeg',
                                                    'uri' => 'https://placehold.it/900x450',
                                                    'filename' => 'image.jpg',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'image',
                                            'id' => 'image1s1',
                                            'doi' => '10.7554/eLife.00001.004',
                                            'label' => 'Image 1 supplement 1',
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
                                                    'id' => 'image1s1-sd1',
                                                    'doi' => '10.7554/eLife.00001.005',
                                                    'label' => 'Image 1 supplement 1 source data 1',
                                                    'title' => 'Image 1 supplement 1 source data 1',
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
    }
}
