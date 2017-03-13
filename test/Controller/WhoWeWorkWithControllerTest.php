<?php

namespace test\eLife\Journal\Controller;

final class WhoWeWorkWithControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_who_we_work_with_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/who-we-work-with');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Who we work with', $crawler->filter('.content-header__title')->text());

        $this->assertNotEmpty($crawler->filter('.grid-listing-heading:contains("Memberships") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.grid-listing-heading:contains("Service providers") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.grid-listing-heading:contains("Content availability and archiving") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.grid-listing-heading:contains("Committees and initiatives") + .grid-listing > .grid-listing-item'));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Who we work with | eLife', $crawler->filter('title')->text());
        $this->assertSame('/who-we-work-with', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/who-we-work-with', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Who we work with', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/who-we-work-with';
    }
}
