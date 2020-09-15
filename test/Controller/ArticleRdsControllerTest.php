<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use test\eLife\Journal\WebTestCase;

/**
 * @backupGlobals enabled
 */
final class ArticleRdsControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_does_not_show_rds_article_if_the_article_has_no_rds()
    {
        $client = static::createClient();

        $this->mockArticle('id-of-article-without-rds');
        $client->request('GET', '/articles/id-of-article-without-rds/executable');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_show_the_rds_article_if_the_article_has_rds()
    {
        $client = static::createClient();

        $this->mockArticle('id-of-article-with-rds');
        $client->request('GET', '/articles/id-of-article-with-rds/executable');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $this->mockArticle('id-of-article-with-rds');
        $crawler = $client->request('GET', '/articles/id-of-article-with-rds/executable?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Article title | eLife', $crawler->filter('title')->text());
        $this->assertSame('/articles/id-of-article-with-rds', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/articles/id-of-article-with-rds', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Article title', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:type"]'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertSame('doi:10.7554/eLife.id-of-article-with-rds', $crawler->filter('meta[name="dc.identifier"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertSame('Article title', $crawler->filter('meta[name="dc.title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertSame('2010-01-01', $crawler->filter('meta[name="dc.date"]')->attr('content'));
        $this->assertSame('© 2010 Bar. Copyright statement.', $crawler->filter('meta[name="dc.rights"]')->attr('content'));
        $this->assertSame('noindex', $crawler->filter('meta[name="robots"]')->attr('content'));
    }

    private function mockArticle($articleId = '00001') : void
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                "http://api.elifesciences.org/articles/{$articleId}",
                ['Accept' => 'application/vnd.elife.article-poa+json; version=3, application/vnd.elife.article-vor+json; version=4']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.article-vor+json; version=4'],
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
