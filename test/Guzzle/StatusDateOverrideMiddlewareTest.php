<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\StatusDateOverrideMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use test\eLife\Journal\Assertions;
use test\eLife\Journal\KernelTestCase;
use test\eLife\Journal\Providers;
use Traversable;
use function GuzzleHttp\Psr7\str;

final class StatusDateOverrideMiddlewareTest extends KernelTestCase
{
    use Assertions;
    use Providers;

    private static $rdsArticles = [
        'poa-with-rds-article' => ['date' => '2030-01-01T00:00:00Z'],
        'vor-with-rds-article' => ['date' => '2030-01-02T00:00:00Z'],
    ];

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_era_articles()
    {
        $middleware = new StatusDateOverrideMiddleware($this->alwaysGrantedAuthorizationChecker());

        $this->assertSame('foo', $middleware(function () {
            return 'foo';
        })());
    }

    /**
     * @test
     * @dataProvider responseProvider
     */
    public function it_rewrites_responses(string $mediaType, array $realResponse, array $expectedResponse)
    {
        $validator = self::bootKernel()->getContainer()->get('elife.api_validator.validator');
        $middleware = new StatusDateOverrideMiddleware($this->alwaysGrantedAuthorizationChecker(), self::$rdsArticles);

        $request = new Request(
            'GET',
            'http://api.elifesciences.org/foo',
            ['Accept' => $mediaType]
        );

        $realResponse = new Response(
            200,
            ['Content-Type' => $mediaType],
            json_encode($realResponse)
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
        return $this->arrayProvider([
            'application/vnd.elife.article-history+json; version=2' => [
                [
                    'versions' => [
                        $this->createArticleVoR('vor-with-rds-article'),
                    ],
                ],
                [
                    'versions' => [
                        $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.article-list+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR('vor-with-rds-article'),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.article-related+json; version=2' => [
                [
                    $this->createArticlePoA('poa-with-rds-article'),
                    $this->createArticleVoR('vor-with-rds-article'),
                ],
                [
                    $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                    $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                ],
            ],
            'application/vnd.elife.article-poa+json; version=3' => [
                $this->createArticlePoA('poa-with-rds-article'),
                $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
            ],
            'application/vnd.elife.article-vor+json; version=7' => [
                $this->createArticleVoR('vor-with-rds-article', null, false),
                $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z', false),
            ],
            'application/vnd.elife.collection+json; version=3' => [
                $this->createCollection('vor-with-rds-article', null, false),
                $this->createCollection('vor-with-rds-article', '2030-01-02T00:00:00Z', false),
            ],
            'application/vnd.elife.collection-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createCollection('vor-with-rds-article', null),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createCollection('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.community-list+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article'),
                        $this->createArticleVoR('vor-with-rds-article'),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                        $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.cover-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover title',
                            'image' => $this->createImage(),
                            'item' => $this->createArticlePoA('poa-with-rds-article'),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover title',
                            'image' => $this->createImage(),
                            'item' => $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                        ],
                    ],
                ],
            ],
            'application/vnd.elife.highlight-list+json; version=3' => [
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Highlight title',
                            'item' => $this->createArticlePoA('poa-with-rds-article'),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Highlight title',
                            'item' => $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                        ],
                    ],
                ],
            ],
            'application/vnd.elife.podcast-episode+json; version=1' => [
                $this->createPodcastEpisode('vor-with-rds-article', null, false),
                $this->createPodcastEpisode('vor-with-rds-article', '2030-01-02T00:00:00Z', false),
            ],
            'application/vnd.elife.press-package+json; version=4' => [
                $this->createPressPackage('vor-with-rds-article', null, false),
                $this->createPressPackage('vor-with-rds-article', '2030-01-02T00:00:00Z', false),
            ],
            'application/vnd.elife.press-package-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createPressPackage('vor-with-rds-article'),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createPressPackage('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.recommendations+json; version=2' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article'),
                        $this->createArticleVoR('vor-with-rds-article'),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                        $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                ],
            ],
            'application/vnd.elife.search+json; version=2;' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article'),
                        $this->createArticleVoR('vor-with-rds-article'),
                    ],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 2,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA('poa-with-rds-article', '2030-01-01T00:00:00Z'),
                        $this->createArticleVoR('vor-with-rds-article', '2030-01-02T00:00:00Z'),
                    ],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 2,
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
                    ],
                ],
            ],
        ]);
    }

    private function createArticleVoR(string $id = null, string $newStatusDate = null, $snippet = true) : array
    {
        $article = [
            'status' => 'vor',
            'stage' => 'published',
            'id' => $id ?? '00001',
            'version' => 1,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.00001',
            'title' => 'Article title',
            'published' => '2010-01-01T00:00:00Z',
            'versionDate' => '2010-01-01T00:00:00Z',
            'statusDate' => $newStatusDate ?? '2010-01-01T00:00:00Z',
            'reviewedDate' => '2010-01-01T00:00:00Z',
            'volume' => 1,
            'elocationId' => 'e00001',
            'copyright' => [
                'license' => 'CC-BY-4.0',
                'holder' => 'Bar',
                'statement' => 'Copyright statement.',
            ],
        ];

        if (!$newStatusDate) {
            $article['statusDate'] = '2010-01-01T00:00:00Z';
        }

        if (!$snippet) {
            $article['body'] = [
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
            ];
        }

        return $article;
    }

    private function createArticlePoA(string $id = null, string $newStatusDate = null) : array
    {
        $article = [
            'status' => 'poa',
            'stage' => 'published',
            'id' => $id ?? '00001',
            'version' => 1,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.00001',
            'title' => 'Article title',
            'published' => '2010-01-01T00:00:00Z',
            'versionDate' => '2010-01-01T00:00:00Z',
            'statusDate' => $newStatusDate ?? '2010-01-01T00:00:00Z',
            'volume' => 1,
            'elocationId' => 'e00001',
            'copyright' => [
                'license' => 'CC-BY-4.0',
                'holder' => 'Bar',
                'statement' => 'Copyright statement.',
            ],
        ];

        if (!$newStatusDate) {
            $article['statusDate'] = '2010-01-01T00:00:00Z';
        }

        return $article;
    }

    private function createCollection(string $id = null, string $newStatusDate = null, bool $snippet = true) : array
    {
        $collection = [
            'id' => '1',
            'title' => 'Collection title',
            'published' => '2010-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => $this->createImage(),
            ],
            'selectedCurator' => $this->createPerson(),
        ];

        if (!$snippet) {
            $collection['content'] = [
                $this->createArticleVoR($id, $newStatusDate),
            ];

            $collection['curators'] = [$collection['selectedCurator']];

            $collection['image']['banner'] = $this->createImage();

            $collection['relatedContent'] = [
                $this->createArticleVoR($id, $newStatusDate),
            ];
        }

        return $collection;
    }

    private function createPodcastEpisode(string $id = null, string $newStatusDate = null, bool $snippet = true) : array
    {
        $podcast = [
            'number' => 1,
            'title' => 'Podcast episode 1',
            'published' => '2010-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => $this->createImage(),
            ],
            'sources' => [
                [
                    'mediaType' => 'audio/mpeg',
                    'uri' => 'https://www.example.com/episode1.mp3',
                ],
            ],
        ];

        if (!$snippet) {
            $podcast['chapters'] = [
                [
                    'number' => 1,
                    'title' => 'Chapter 1',
                    'time' => 0,
                    'content' => [
                        $this->createArticleVoR($id, $newStatusDate, true),
                    ],
                ],
            ];

            $podcast['image']['banner'] = $this->createImage();
        }

        return $podcast;
    }

    private function createPressPackage(string $id = null, string $newStatusDate = null, bool $snippet = true) : array
    {
        $pressPackage = [
            'id' => '1',
            'title' => 'Press package title',
            'published' => '2010-01-01T00:00:00Z',
        ];

        if (!$snippet) {
            $pressPackage['content'] = [
                [
                    'type' => 'paragraph',
                    'text' => 'text',
                ],
            ];

            $pressPackage['relatedContent'] = [
                $this->createArticleVoR($id, $newStatusDate),
            ];
        }

        return $pressPackage;
    }

    private function createImage() : array
    {
        return [
            'uri' => 'https://www.example.com/iiif/iden%2Ftifer',
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
        ];
    }

    private function createPerson() : array
    {
        return [
            'id' => 'person1',
            'type' => [
                'id' => 'senior-editor',
                'label' => 'Senior Editor',
            ],
            'name' => [
                'preferred' => 'Person 1',
                'index' => 'Person 1',
            ],
        ];
    }
}
