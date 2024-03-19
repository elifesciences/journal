<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

/**
 * @backupGlobals enabled
 */
final class ArticleEraDownloadControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_does_not_download_era_article_if_the_article_has_no_era()
    {
        $client = static::createClient();

        $this->mockArticle('id-of-article-without-era');
        $client->request('GET', '/articles/id-of-article-without-era/executable/download');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_download_the_era_article_if_the_article_has_era()
    {
        $client = static::createClient();

        $this->mockArticle('id-of-article-with-era');
        $client->request('GET', '/articles/id-of-article-with-era/executable/download');

        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertStringStartsWith('http://localhost/download/aHR0cHM6Ly9yZXByby5lbGlmZXNjaWVuY2VzLm9yZy9hcmNoaXZlLnppcA--/elife-id-of-article-with-era-v3-era.zip', $client->getResponse()->headers->get('Location'));
    }

    private function mockArticle($articleId = '00001') : void
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/{$articleId}",
                ['Accept' => 'application/vnd.elife.article-poa+json; version=4, application/vnd.elife.article-vor+json; version=8']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=8'],
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
                            'title' => 'Section',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Text.',
                                ],
                            ],
                        ],
                    ],
                ])
            )
        );
    }
}
