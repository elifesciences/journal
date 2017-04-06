<?php

namespace test\eLife\Journal\Controller;

final class TermsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_terms_and_policy_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Terms and policy', $crawler->filter('.content-header__title')->text());

        $majorSections = $crawler->filter('h2.article-section__header_text');

        $this->assertCount(2, $majorSections);

        $this->assertSame('Terms and Conditions of Use', trim($majorSections->eq(0)->text()));
        $this->assertSame('Privacy Policy', trim($majorSections->eq(1)->text()));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Terms and policy | eLife', $crawler->filter('title')->text());
        $this->assertSame('/terms', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/terms', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Terms and policy', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
    }

    protected function getUrl() : string
    {
        return '/terms';
    }
}
