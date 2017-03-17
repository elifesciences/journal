<?php

namespace test\eLife\Journal\Controller;

final class AboutPeopleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_people_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('People', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('People | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/people', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/people', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('People', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/about/people';
    }
}
