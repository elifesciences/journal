<?php

namespace test\eLife\Journal\Controller;

final class ResourcesControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_resources_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resources');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Resources', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Resources | eLife', $crawler->filter('title')->text());
        $this->assertSame('/resources', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/resources', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Resources', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/resources';
    }
}
