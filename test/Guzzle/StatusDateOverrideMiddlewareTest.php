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
        'with-rds-article' => ['date' => '2030-01-01T00:00:00Z'],
        'with-rds-article-without-date' => [],
    ];

    /**
     * @test
     */
    public function it_does_nothing_if_there_are_no_rds_articles()
    {
        $middleware = new StatusDateOverrideMiddleware($this->stubAuthorizationChecker());

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
        $middleware = new StatusDateOverrideMiddleware($this->stubAuthorizationChecker(), self::$rdsArticles);

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
                        $this->createArticleVoR('with-rds-article'),
                    ],
                ],
                [
                    'versions' => [
                        $this->createArticleVoR('with-rds-article', '2030-01-01T00:00:00Z'),
                    ],
                ],
            ],
        ]);
    }

    private function createArticleVoR(string $id = null, string $newStatusDate = null) : array
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
}
