<?php

namespace test\eLife\Journal\Controller;

final class AboutPeerReviewControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_peer_review_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Peer review', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Peer review | About | eLife', $crawler->filter('title')->text());
        $this->assertSame('/about/peer-review', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/about/peer-review', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Peer review', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('"Our goal at eLife is to publish papers that our reviewers and editors find authoritative, rigorous, insightful, enlightening or just beautiful"*', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('"Our goal at eLife is to publish papers that our reviewers and editors find authoritative, rigorous, insightful, enlightening or just beautiful"*', $crawler->filter('meta[name="description"]')->attr('content'));
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
        return '/about/peer-review';
    }
}
