<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Guzzle\SubjectRewritingMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use test\eLife\Journal\Assertions;
use test\eLife\Journal\KernelTestCase;
use test\eLife\Journal\Providers;
use Traversable;
use function GuzzleHttp\Psr7\str;

final class SubjectRewritingMiddlewareTest extends KernelTestCase
{
    use Assertions;
    use Providers;

    private static $rewrites = [
        ['from_id' => 'old-subject', 'to_id' => 'new-subject', 'to_name' => 'New Subject'],
    ];

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_rewrites()
    {
        $middleware = new SubjectRewritingMiddleware();

        $this->assertSame('foo', $middleware(function () {
            return 'foo';
        })());
    }

    /**
     * @test
     */
    public function it_changes_highlights_targets()
    {
        $middleware = new SubjectRewritingMiddleware(self::$rewrites);

        $middleware(function (RequestInterface $request) {
            $this->assertSameUri('http://api.elifesciences.org/highlights/new-subject', $request->getUri());

            return new Response();
        })(new Request('GET', 'http://api.elifesciences.org/highlights/old-subject'))->wait();
    }

    /**
     * @test
     */
    public function it_changes_subject_targets()
    {
        $middleware = new SubjectRewritingMiddleware(self::$rewrites);

        $middleware(function (RequestInterface $request) {
            $this->assertSameUri('http://api.elifesciences.org/subjects/new-subject', $request->getUri());

            return new Response();
        })(new Request('GET', 'http://api.elifesciences.org/subjects/old-subject'))->wait();
    }

    /**
     * @test
     */
    public function it_adds_to_query_strings()
    {
        $middleware = new SubjectRewritingMiddleware(self::$rewrites);

        $middleware(function (RequestInterface $request) {
            $this->assertSameUri('http://api.elifesciences.org/subjects?subject[]=old-subject&subject[]=new-subject', $request->getUri());

            return new Response();
        })(new Request('GET', 'http://api.elifesciences.org/subjects?subject[]=old-subject'))->wait();

        $middleware(function (RequestInterface $request) {
            $this->assertSameUri('http://api.elifesciences.org/subjects?subject[]=new-subject&subject[]=old-subject', $request->getUri());

            return new Response();
        })(new Request('GET', 'http://api.elifesciences.org/subjects?subject[]=new-subject'))->wait();
    }

    /**
     * @test
     * @dataProvider responseProvider
     */
    public function it_rewrites_responses(string $mediaType, array $realResponse, array $expectedResponse)
    {
        $validator = self::bootKernel()->getContainer()->get('elife.api_validator.validator');
        $middleware = new SubjectRewritingMiddleware(self::$rewrites);

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
                        $this->createArticleVoR(),
                    ],
                ],
                [
                    'versions' => [
                        $this->createArticleVoR(true),
                    ],
                ],
            ],
            'application/vnd.elife.article-list+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR(),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticleVoR(true),
                    ],
                ],
            ],
            'application/vnd.elife.article-related+json; version=1' => [
                [
                    $this->createArticlePoA(),
                    $this->createArticleVoR(),
                ],
                [
                    $this->createArticlePoA(true),
                    $this->createArticleVoR(true),
                ],
            ],
            'application/vnd.elife.article-poa+json; version=2' => [
                $this->createArticlePoA(false, false),
                $this->createArticlePoA(true, false),
            ],
            'application/vnd.elife.article-poa+json; version=3' => [
                $this->createArticlePoA(false, false),
                $this->createArticlePoA(true, false),
            ],
            'application/vnd.elife.article-vor+json; version=5' => [
                $this->createArticleVoR(false, false),
                $this->createArticleVoR(true, false),
            ],
            'application/vnd.elife.article-vor+json; version=6' => [
                $this->createArticleVoR(false, false),
                $this->createArticleVoR(true, false),
            ],
            'application/vnd.elife.blog-article+json; version=2' => [
                $this->createBlogArticle(false, false),
                $this->createBlogArticle(true, false),
            ],
            'application/vnd.elife.blog-article-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createBlogArticle(false),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createBlogArticle(true),
                    ],
                ],
            ],
            'application/vnd.elife.collection+json; version=1' => [
                $this->createCollection(false, false),
                $this->createCollection(true, false),
            ],
            'application/vnd.elife.collection-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createCollection(false),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createCollection(true),
                    ],
                ],
            ],
            'application/vnd.elife.community-list+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(false),
                        $this->createArticleVoR(false),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(true),
                        $this->createArticleVoR(true),
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
                            'item' => $this->createArticlePoA(false),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Cover title',
                            'image' => $this->createImage(),
                            'item' => $this->createArticlePoA(true),
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
                            'item' => $this->createArticlePoA(false),
                        ],
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Highlight title',
                            'item' => $this->createArticlePoA(true),
                        ],
                    ],
                ],
            ],
            'application/vnd.elife.person+json; version=1' => [
                $this->createPerson(false, false),
                $this->createPerson(true, false),
            ],
            'application/vnd.elife.person-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createPerson(false, false),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createPerson(true, false),
                    ],
                ],
            ],
            'application/vnd.elife.podcast-episode+json; version=1' => [
                $this->createPodcastEpisode(false, false),
                $this->createPodcastEpisode(true, false),
            ],
            'application/vnd.elife.press-package+json; version=3' => [
                $this->createPressPackage(false, false),
                $this->createPressPackage(true, false),
            ],
            'application/vnd.elife.press-package-list+json; version=1' => [
                [
                    'total' => 1,
                    'items' => [
                        $this->createPressPackage(false),
                    ],
                ],
                [
                    'total' => 1,
                    'items' => [
                        $this->createPressPackage(true),
                    ],
                ],
            ],
            'application/vnd.elife.recommendations+json; version=1' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(false),
                        $this->createArticleVoR(false),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(true),
                        $this->createArticleVoR(true),
                    ],
                ],
            ],
            'application/vnd.elife.search+json; version=2; with new subject' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(false),
                        $this->createArticleVoR(false),
                    ],
                    'subjects' => [
                        $this->createSubject('old-subject', 'Old Subject') + ['results' => 2],
                        $this->createSubject('new-subject', 'New Subject') + ['results' => 4],
                        $this->createSubject('other-subject', 'Other Subject') + ['results' => 8],
                    ],
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
                        $this->createArticlePoA(true),
                        $this->createArticleVoR(true),
                    ],
                    'subjects' => [
                        $this->createSubject('new-subject', 'New Subject') + ['results' => 6],
                        $this->createSubject('other-subject', 'Other Subject') + ['results' => 8],
                    ],
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
            'application/vnd.elife.search+json; version=2; without new subject' => [
                [
                    'total' => 2,
                    'items' => [
                        $this->createArticlePoA(false),
                        $this->createArticleVoR(false),
                    ],
                    'subjects' => [
                        $this->createSubject('old-subject', 'Old Subject') + ['results' => 2],
                        $this->createSubject('other-subject', 'Other Subject') + ['results' => 8],
                    ],
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
                        $this->createArticlePoA(true),
                        $this->createArticleVoR(true),
                    ],
                    'subjects' => [
                        $this->createSubject('other-subject', 'Other Subject') + ['results' => 8],
                        $this->createSubject('new-subject', 'New Subject') + ['results' => 2],
                    ],
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
            'application/vnd.elife.subject-list+json; version=1' => [
                [
                    'total' => 3,
                    'items' => [
                        $this->createSubject('old-subject', 'Old Subject', false),
                        $this->createSubject('new-subject', 'New Subject', false),
                        $this->createSubject('other-subject', 'Other Subject', false),
                    ],
                ],
                [
                    'total' => 2,
                    'items' => [
                        $this->createSubject('new-subject', 'New Subject', false),
                        $this->createSubject('other-subject', 'Other Subject', false),
                    ],
                ],
            ],
        ]);
    }

    private function createArticlePoA(bool $rewritten = false, bool $snippet = true) : array
    {
        $article = [
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
                'holder' => 'Bar',
                'statement' => 'Copyright statement.',
            ],
            'subjects' => [
                $this->createSubject('new-subject', 'New Subject'),
                $this->createSubject('other-subject', 'Other Subject'),
            ],
        ];

        if (!$rewritten) {
            $article['subjects'][] = $this->createSubject('old-subject', 'Old Subject');
        }

        return $article;
    }

    private function createArticleVoR(bool $rewritten = false, bool $snippet = true) : array
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
            'subjects' => [
                $this->createSubject('new-subject', 'New Subject'),
                $this->createSubject('other-subject', 'Other Subject'),
            ],
        ];

        if (!$rewritten) {
            $article['subjects'][] = $this->createSubject('old-subject', 'Old Subject');
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

    private function createBlogArticle(bool $rewritten = false, bool $snippet = true) : array
    {
        $article = [
            'id' => '1',
            'title' => 'Blog article title',
            'published' => '2010-01-01T00:00:00Z',
            'subjects' => [
                $this->createSubject('new-subject', 'New Subject'),
                $this->createSubject('other-subject', 'Other Subject'),
            ],
        ];

        if (!$rewritten) {
            $article['subjects'][] = $this->createSubject('old-subject', 'Old Subject');
        }

        if (!$snippet) {
            $article['content'] = [
                [
                    'type' => 'paragraph',
                    'text' => 'text',
                ],
            ];
        }

        return $article;
    }

    private function createCollection(bool $rewritten = false, bool $snippet = true) : array
    {
        $collection = [
            'id' => '1',
            'title' => 'Collection title',
            'published' => '2010-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => $this->createImage(),
            ],
            'selectedCurator' => $this->createPerson($rewritten, true),
            'subjects' => [
                $this->createSubject('new-subject', 'New Subject'),
                $this->createSubject('other-subject', 'Other Subject'),
            ],
        ];

        if (!$rewritten) {
            $collection['subjects'][] = $this->createSubject('old-subject', 'Old Subject');
        }

        if (!$snippet) {
            $collection['content'] = [
                $this->createArticleVoR($rewritten),
                $this->createBlogArticle($rewritten) + ['type' => 'blog-article'],
            ];

            $collection['curators'] = [$collection['selectedCurator']];

            $collection['image']['banner'] = $this->createImage();

            $collection['relatedContent'] = [
                $this->createArticleVoR($rewritten),
                $this->createBlogArticle($rewritten) + ['type' => 'blog-article'],
            ];
        }

        return $collection;
    }

    private function createPerson(bool $rewritten = false, bool $snippet = true) : array
    {
        $person = [
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

        if (!$snippet) {
            $person['research'] = [
                'expertises' => [
                    $this->createSubject('new-subject', 'New Subject'),
                    $this->createSubject('other-subject', 'Other Subject'),
                ],
                'focuses' => [],
            ];

            if (!$rewritten) {
                $person['research']['expertises'][] = $this->createSubject('old-subject', 'Old Subject');
            }
        }

        return $person;
    }

    private function createPodcastEpisode(bool $rewritten = false, bool $snippet = true) : array
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
                        $this->createArticlePoA($rewritten, true),
                        $this->createArticleVoR($rewritten, true),
                    ],
                ],
            ];

            $podcast['image']['banner'] = $this->createImage();
        }

        return $podcast;
    }

    private function createPressPackage(bool $rewritten = false, bool $snippet = true) : array
    {
        $pressPackage = [
            'id' => '1',
            'title' => 'Press package title',
            'published' => '2010-01-01T00:00:00Z',
            'subjects' => [
                $this->createSubject('new-subject', 'New Subject'),
                $this->createSubject('other-subject', 'Other Subject'),
            ],
        ];

        if (!$rewritten) {
            $pressPackage['subjects'][] = $this->createSubject('old-subject', 'Old Subject');
        }

        if (!$snippet) {
            $pressPackage['content'] = [
                [
                    'type' => 'paragraph',
                    'text' => 'text',
                ],
            ];

            $pressPackage['relatedContent'] = [
                $this->createArticlePoA($rewritten),
                $this->createArticleVoR($rewritten),
            ];
        }

        return $pressPackage;
    }

    private function createSubject(string $id, string $name, bool $snippet = true) : array
    {
        $subject = [
            'id' => $id,
            'name' => $name,
        ];

        if (!$snippet) {
            $subject['impactStatement'] = 'Subject impact statement.';
            $subject['image'] = [
                'banner' => $this->createImage(),
                'thumbnail' => $this->createImage(),
            ];
        }

        return $subject;
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
}
