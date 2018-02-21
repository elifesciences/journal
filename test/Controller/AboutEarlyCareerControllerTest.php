<?php

namespace test\eLife\Journal\Controller;

final class AboutEarlyCareerControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_innovation_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Early-careers', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Early-careers | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/early-career', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/early-career', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Early-careers', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('The community behind eLife wants to help address some of the pressures on early-career scientists', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('The community behind eLife wants to help address some of the pressures on early-career scientists', $crawler->filter('meta[name="description"]')->attr('content'));
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
        return '/about/early-career';
    }
}
