<?php

namespace test\eLife\Journal\Controller;

final class AboutTechnologyControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_technology_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Technology', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Technology | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/technology', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/technology', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Technology', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife’s open-source technology initiatives enhance the communication and use of research results online.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife’s open-source technology initiatives enhance the communication and use of research results online.', $crawler->filter('meta[name="description"]')->attr('content'));
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
        return '/about/technology';
    }
}
