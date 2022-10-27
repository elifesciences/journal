<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class DigestsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_digests_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife Science Digests', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No digests available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('eLife Science Digests | eLife', $crawler->filter('title')->text());
        $this->assertSame('/digests', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/digests', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('eLife Science Digests', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Cutting jargon and putting research in context, digests showcase some of the latest articles published in eLife.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Cutting jargon and putting research in context, digests showcase some of the latest articles published in eLife.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('280', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('200', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('application/rss+xml', $crawler->filter('link[rel="alternate"]')->attr('type'));
        $this->assertSame('eLife Sciences Digests', $crawler->filter('link[rel="alternate"]')->attr('title'));
        $this->assertSame('/rss/digests.xml', $crawler->filter('link[rel="alternate"]')->attr('href'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    /**
     * @test
     * @dataProvider invalidPageProvider
     */
    public function it_displays_a_404_when_not_on_a_valid_page($page, callable $callable = null)
    {
        $client = static::createClient();

        if ($callable) {
            $callable();
        }

        $client->request('GET', '/digests?page='.$page);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', 'foo'] as $page) {
            yield 'page '.$page => [$page];
        }

        foreach (['2'] as $page) {
            yield 'page '.$page => [
                $page,
                function () use ($page) {
                    $this->mockApiResponse(
                        new Request(
                            'GET',
                            'http://api.elifesciences.org/digests?page=1&per-page=1&order=desc',
                            ['Accept' => 'application/vnd.elife.digest-list+json; version=1']
                        ),
                        new Response(
                            404,
                            ['Content-Type' => 'application/problem+json'],
                            json_encode(['title' => 'Not found'])
                        )
                    );
                },
            ];
        }
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests?page=1&per-page=8&order=desc',
                ['Accept' => 'application/vnd.elife.digest-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.digest-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/digests';
    }
}
