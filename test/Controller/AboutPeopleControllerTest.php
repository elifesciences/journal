<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class AboutPeopleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_people_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Leadership team', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Leadership team | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/people', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/people', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Leadership team', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('The working scientists who serve as eLife editors, our early-career advisors, governing board, and our executive staff all work in concert to realise eLife’s mission to accelerate discovery', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('The working scientists who serve as eLife editors, our early-career advisors, governing board, and our executive staff all work in concert to realise eLife’s mission to accelerate discovery', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:image"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type=leadership',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page=1&per-page=1&order=asc&type=senior-editor',
                ['Accept' => 'application/vnd.elife.person-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.person-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/about/people';
    }
}
