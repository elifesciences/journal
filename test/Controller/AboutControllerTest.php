<?php

namespace test\eLife\Journal\Controller;

use Symfony\Bridge\PhpUnit\ClockMock;

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
        $this->assertSame('About eLife', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     * @group time-sensitive
     */
    public function it_does_not_display_wallenberg_information_early()
    {
        ClockMock::withClockMock(strtotime('2017-12-31T23:59:59Z'));

        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertNotContains('Wallenberg', $crawler->text());
    }

    /**
     * @test
     * @group time-sensitive
     */
    public function it_displays_wallenberg_information_from_2018()
    {
        ClockMock::withClockMock(strtotime('2018-01-01T00:00:00Z'));

        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Wallenberg', $crawler->text());
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
        $this->assertSame('We want to improve all aspects of research communication in support of excellence in science', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('We want to improve all aspects of research communication in support of excellence in science', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/about';
    }
}
