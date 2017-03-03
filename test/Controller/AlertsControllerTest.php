<?php

namespace test\eLife\Journal\Controller;

final class AlertsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_alerts_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/alerts');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Alerts', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Alerts | eLife', $crawler->filter('title')->text());
        $this->assertSame('/alerts', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/alerts', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Alerts', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/alerts';
    }
}
