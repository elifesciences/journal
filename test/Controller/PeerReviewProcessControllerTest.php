<?php

namespace test\eLife\Journal\Controller;

final class PeerReviewProcessControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_privacy_notice_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife’s peer review process', $crawler->filter('.content-header__title')->text());

        $majorSections = $crawler->filter('h2.peer-review-process-page-process-outer-section-heading');

        $this->assertCount(4, $majorSections);

        $this->assertSame([
            'Submission',
            'Peer Review',
            'Reviewed Preprint',
            'Version of Record',
        ], $majorSections->extract('_text'));
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('eLife’s peer review process | eLife', $crawler->filter('title')->text());
        $this->assertSame('/peer-review-process', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/peer-review-process', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('eLife’s peer review process', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertSame('eLife is changing its editorial process to emphasize public reviews and assessments of preprints by eliminating accept/reject decisions after peer review.To learn more about why eLife’s process is changing, read the Editorial.', $crawler->filter('meta[property="og:description"]')->attr('content'));
        $this->assertSame('eLife is changing its editorial process to emphasize public reviews and assessments of preprints by eliminating accept/reject decisions after peer review.To learn more about why eLife’s process is changing, read the Editorial.', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('summary', $crawler->filter('meta[name="twitter:card"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[name="twitter:image"]')->attr('content'));
        $this->assertSame('http://localhost/'.ltrim(self::$kernel->getContainer()->get('elife.assets.packages')->getUrl('assets/images/social/icon-280x200@1.c5b562b1.png'), '/'), $crawler->filter('meta[property="og:image"]')->attr('content'));
        $this->assertSame('280', $crawler->filter('meta[property="og:image:width"]')->attr('content'));
        $this->assertSame('200', $crawler->filter('meta[property="og:image:height"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[name="dc.identifier"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.relation.ispartof"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.title"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.description"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.date"]'));
        $this->assertEmpty($crawler->filter('meta[name="dc.rights"]'));
    }

    protected function getUrl() : string
    {
        return '/peer-review-process';
    }
}
