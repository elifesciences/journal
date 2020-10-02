<?php

namespace test\eLife\Journal\Controller;

final class TermsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_terms_and_conditions_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Terms and conditions', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Terms and conditions | eLife', $crawler->filter('title')->text());
        $this->assertSame('/terms', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/terms', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Terms and conditions', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:description"]'));
        $this->assertEmpty($crawler->filter('meta[name="description"]'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('600', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    protected function getUrl() : string
    {
        return '/terms';
    }
}
