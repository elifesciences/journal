<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use test\eLife\Journal\WebTestCase;

final class ArticleXmlTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_displays_xml()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://www.example.com/xml',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/xml'],
                '<xml></xml>'
            )
        );

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->getUrl());
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['application/xml'],
        ], $response->headers->all());
        $this->assertSame('<xml></xml>', $content);
    }

    /**
     * @test
     */
    public function it_displays_previous_versions()
    {
        $client = static::createClient();

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://www.example.com/xml1',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'X-Forwarded-For' => '127.0.0.1',
                    'X-Forwarded-Host' => 'localhost',
                    'X-Forwarded-Port' => '80',
                    'X-Forwarded-Proto' => 'http',
                ]
            ),
            new Response(
                200,
                ['Content-Type' => 'application/xml'],
                '<xml></xml>'
            )
        );

        $content = $this->captureContent(function () use ($client) {
            $client->request('GET', $this->getPreviousVersionUrl());
        });

        $response = $client->getResponse();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArraySubset([
            'content-type' => ['application/xml'],
        ], $response->headers->all());
        $this->assertSame('<xml></xml>', $content);
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=5'],
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
                            'xml' => 'http://www.example.com/xml',
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

        return '/articles/00001.xml';
    }

    private function getPreviousVersionUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/00001/versions/1',
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=5']
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
                    'xml' => 'http://www.example.com/xml1',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'holder' => 'Author One',
                        'statement' => 'Copyright statement.',
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
                            'xml' => 'http://www.example.com/xml1',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
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
                            'xml' => 'http://www.example.com/xml2',
                            'copyright' => [
                                'license' => 'CC-BY-4.0',
                                'holder' => 'Author One',
                                'statement' => 'Copyright statement.',
                            ],
                        ],
                    ],
                ])
            )
        );

        return '/articles/00001v1.xml';
    }

    private function captureContent(callable $callback) : string
    {
        ob_start();
        $callback();

        return ob_get_clean();
    }
}
