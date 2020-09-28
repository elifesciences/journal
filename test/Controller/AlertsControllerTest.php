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

        $majorSections = $crawler->filter('h2.article-section__header_text');

        $this->assertCount(5, $majorSections);

        $this->assertSame('New Research', trim($majorSections->eq(0)->text()));
        $this->assertSame('Science in plain language', trim($majorSections->eq(1)->text()));
        $this->assertSame('Community-building', trim($majorSections->eq(2)->text()));
        $this->assertSame('eLife\'s Innovation Initiative and technology news', trim($majorSections->eq(3)->text()));
        $this->assertSame('The latest from eLife', trim($majorSections->eq(4)->text()));
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
        $this->assertSame('Stay in touch with eLife efforts to support the community and open science as well as new research. Choose your feeds and preferred ways to connect below.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('Stay in touch with eLife efforts to support the community and open science as well as new research. Choose your feeds and preferred ways to connect below.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary_large_image', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-1200x630@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('1200', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('630', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    protected function getUrl() : string
    {
        return '/alerts';
    }
}
