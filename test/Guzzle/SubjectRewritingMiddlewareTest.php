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

    private static $rewrite = [
        'old-subject' => ['id' => 'new-subject', 'name' => 'New Subject'],
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
        $middleware = new SubjectRewritingMiddleware(self::$rewrite);

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
        $middleware = new SubjectRewritingMiddleware(self::$rewrite);

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
        $middleware = new SubjectRewritingMiddleware(self::$rewrite);

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
        $middleware = new SubjectRewritingMiddleware(self::$rewrite);

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
            'application/vnd.elife.article-history+json; version=1' => [
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
            'application/vnd.elife.article-poa+json; version=1' => [
                $this->createArticlePoA(false, false),
                $this->createArticlePoA(true, false),
            ],
            'application/vnd.elife.article-poa+json; version=2' => [
                $this->createArticlePoA(false, false),
                $this->createArticlePoA(true, false),
            ],
            'application/vnd.elife.article-vor+json; version=1' => [
                $this->createArticleVoR(false, false),
                $this->createArticleVoR(true, false),
            ],
            'application/vnd.elife.article-vor+json; version=2' => [
                $this->createArticleVoR(false, false),
                $this->createArticleVoR(true, false),
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

    private function createSubject(string $id, string $name, bool $snippet = true) : array
    {
        $subject = [
            'id' => $id,
            'name' => $name,
        ];

        if (!$snippet) {
            $subject['impactStatement'] = 'Subject impact statement.';
            $subject['image'] = [
                'banner' => [
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
                'thumbnail' => [
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
            ];
        }

        return $subject;
    }
}
