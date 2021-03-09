<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\ReferenceAuthorNameRewriterMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use test\eLife\Journal\KernelTestCase;
use Traversable;
use function GuzzleHttp\Psr7\str;

final class ReferenceAuthorNameRewriterMiddlewareTest extends KernelTestCase
{
    /**
     * @test
     * @dataProvider responseProvider
     */
    public function it_rewrites_responses(string $mediaType, array $realReferences, array $expectedResponse)
    {
        $validator = self::bootKernel()->getContainer()->get('elife.api_validator.validator');
        $middleware = new ReferenceAuthorNameRewriterMiddleware();

        $request = new Request(
            'GET',
            'http://api.elifesciences.org/foo',
            ['Accept' => $mediaType]
        );

        $realResponse = new Response(
            200,
            ['Content-Type' => $mediaType],
            json_encode($this->createArticleVoR($realReferences))
        );
        $expectedResponse = new Response(
            200,
            ['Content-Type' => $mediaType],
            json_encode($expectedResponse)
        );

        $validator->validate($realResponse);
        $validator->validate($expectedResponse);

        $actualResponse = $middleware(function (RequestInterface $realRequest) use ($request, $realResponse) {
            $this->assertSame(str($request), str($realRequest));

            return $realResponse;
        })($request)->wait();

        $this->assertSame(str($expectedResponse), str($actualResponse));
    }

    public function responseProvider() : Traversable
    {
        yield 'no references' => [
            'application/vnd.elife.article-vor+json; version=4',
            [],
            $this->createArticleVoR([]),
        ];
        yield 'references' => [
            'application/vnd.elife.article-vor+json; version=4',
            [
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
                    [
                        'type' => 'journal',
                        'id' => 'bib2',
                        'date' => '2014',
                        'authors' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'One Person',
                                    'index' => 'One, Person',
                                ],
                            ],
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'Two Person',
                                    'index' => 'Two, Person',
                                ],
                            ],
                        ],
                        'articleTitle' => 'Journal article',
                        'journal' => 'A journal',
                        'pages' => 'In press',
                    ],
                ],
            ],
            $this->createArticleVoR([
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
                    [
                        'type' => 'journal',
                        'id' => 'bib2',
                        'date' => '2014',
                        'authors' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'One Person',
                                    'index' => 'One, Person',
                                ],
                            ],
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'Two Person',
                                    'index' => 'Two, Person',
                                ],
                            ],
                        ],
                        'articleTitle' => 'Journal article',
                        'journal' => 'A journal',
                        'pages' => 'In press',
                    ],
                ],
            ]),
        ];
        yield 'datasets' => [
            'application/vnd.elife.article-vor+json; version=4',
            [
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
                                        'preferred' => 'Bar Foo',
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
                                        'preferred' => 'Bar Foo',
                                        'index' => 'Bar, Foo',
                                    ],
                                ],
                            ],
                            'date' => '2014',
                            'title' => 'Data set 2',
                        ],
                    ],
                ],
            ],
            $this->createArticleVoR([
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
                                        'preferred' => 'Bar Foo',
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
                                        'preferred' => 'Bar Foo',
                                        'index' => 'Bar, Foo',
                                    ],
                                ],
                            ],
                            'date' => '2014',
                            'title' => 'Data set 2',
                        ],
                    ],
                ],
            ]),
        ];
    }

    private function createArticleVoR(array $references = []) : array
    {
        $article = [
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
                    'id' => 's1',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
        ] + $references;

        return $article;
    }
}
