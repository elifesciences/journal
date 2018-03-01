<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class AboutAimsScopesControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_aims_and_scopes_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Aims and scopes', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Aims and scopes | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/aims-scopes', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/aims-scopes', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Aims and scopes', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, and Research Advances (read more about article types) in the following subject areas.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, and Research Advances (read more about article types) in the following subject areas.', $crawler->filter('meta[name="description"]')->attr('content'));
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
                'http://api.elifesciences.org/subjects?page=1&per-page=100&order=desc',
                ['Accept' => 'application/vnd.elife.subject-list+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.subject-list+json; version=1'],
                json_encode([
                    'total' => 0,
                    'items' => [],
                ])
            )
        );

        return '/about/aims-scopes';
    }
}
