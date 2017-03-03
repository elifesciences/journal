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

        $crawler = $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('About eLife', $crawler->filter('.content-header__title')->text());
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
        $this->assertSame('Pain-free publishing for your best science.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/about';
    }
}
