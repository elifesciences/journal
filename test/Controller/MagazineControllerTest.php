<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class MagazineControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_magazine_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Magazine', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No articles available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_displays_the_magazine_navigation()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $sections = $crawler->filter('.section-listing__list_item');

        $this->assertCount(7, $sections);
        $this->assertSame('Editorials', trim($sections->eq(0)->text()));
        $this->assertSame('Insights', trim($sections->eq(1)->text()));
        $this->assertSame('Feature Articles', trim($sections->eq(2)->text()));
        $this->assertSame('Podcasts', trim($sections->eq(3)->text()));
        $this->assertSame('Collections', trim($sections->eq(4)->text()));
        $this->assertSame('Community', trim($sections->eq(5)->text()));
        $this->assertSame('Digests', trim($sections->eq(6)->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Magazine | eLife', $crawler->filter('title')->text());
        $this->assertSame('/magazine', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/magazine', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Magazine', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Highlighting the latest research and giving a voice to scientists', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Highlighting the latest research and giving a voice to scientists', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/banners/magazine-1114x336@1.jpg'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1114', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('336', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertSame('application/rss+xml', $crawler->filter('link[rel="alternate"]')->attr('type'));
        $this->assertSame('Insights into science from eLife', $crawler->filter('link[rel="alternate"]')->attr('title'));
        $this->assertSame('/rss/magazine.xml', $crawler->filter('link[rel="alternate"]')->attr('href'));
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

        $client->request('GET', '/magazine?page='.$page);

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
                            'http://api.elifesciences.org/search?for=&page=1&per-page=1&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default',
                            ['Accept' => 'application/vnd.elife.search+json; version=1']
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
                'http://api.elifesciences.org/search?for=&page=1&per-page=10&sort=date&order=desc&type[]=editorial&type[]=insight&type[]=feature&type[]=collection&type[]=interview&type[]=podcast-episode&use-date=default',
                ['Accept' => 'application/vnd.elife.search+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.search+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                    'subjects' => [],
                    'types' => [
                        'correction' => 0,
                        'editorial' => 0,
                        'feature' => 0,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
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
                    ],
                ])
            )
        );

        return '/magazine';
    }
}
