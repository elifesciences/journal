<?php

namespace test\eLife\Journal\Controller;

final class AboutInnovationControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_innovation_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Innovation', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Innovation | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/innovation', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/innovation', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Innovation', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife invests in open-source technology to deliver effective solutions to accelerate research communication and discovery', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife invests in open-source technology to deliver effective solutions to accelerate research communication and discovery', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/about/innovation';
    }
}
