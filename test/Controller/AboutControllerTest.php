<?php

namespace test\eLife\Journal\Controller;

final class AboutControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_about_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife: Accelerating discovery', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('About', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife is an initiative from research funders to transform research communication through improvements to science publishing, technology and research culture.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife is an initiative from research funders to transform research communication through improvements to science publishing, technology and research culture.', $crawler->filter('meta[name="description"]')->attr('content'));
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
        return '/about';
    }
}
