<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Traversable;

final class LabsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_empty_labs_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife Labs', $crawler->filter('.content-header__title')->text());
        $this->assertContains('No posts available.', $crawler->filter('main')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Labs | eLife', $crawler->filter('title')->text());
        $this->assertSame('/labs', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/labs', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Labs', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('Exploring open-source solutions at the intersection of research and technology. Learn more about innovation at eLife, follow us on Twitter, or sign up for our technology and innovation newsletter.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Exploring open-source solutions at the intersection of research and technology. Learn more about innovation at eLife, follow us on Twitter, or sign up for our technology and innovation newsletter.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('assets.packages')->getUrl('assets/images/banners/labs-1114x336@1.jpg'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1114', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('336', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
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

        $client->request('GET', '/labs?page='.$page);

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
                            'http://api.elifesciences.org/labs-posts?page=1&per-page=1&order=desc',
                            ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
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
                'http://api.elifesciences.org/labs-posts?page=1&per-page=8&order=desc',
                ['Accept' => 'application/vnd.elife.labs-post-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.labs-post-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/labs';
    }
}
