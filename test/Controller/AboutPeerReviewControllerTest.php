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
        $this->assertSame('eLife\'s review process combines the immediacy and openness of preprints with the scrutiny of peer review by experts.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife\'s review process combines the immediacy and openness of preprints with the scrutiny of peer review by experts.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-600x600@1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
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

    /**
     * @test
     */
    public function it_displays_announcement_info_bar()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'eLife\'s peer-review process is changing. From January 2023, eLife will no longer make accept/reject decisions after peer review. Instead, every preprint sent for peer review will be published on the eLife website as a “Reviewed Preprint” that includes an eLife assessment, public reviews, and a response from the authors (if available). When writing the eLife assessment, the editors and reviewers will use a common vocabulary to summarize the significance of the findings and the strength of the evidence reported in the preprint. <a href="/inside-elife/54d63486">Read about the new process</a>.',
            $crawler->filter('.content-container .info-bar--announcement .info-bar__text')->html()
        );
    }

    protected function getUrl() : string
    {
        return '/about/peer-review';
    }
}
