<?php

namespace test\eLife\Journal\Controller;

final class WhoWeWorkWithControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_who_we_work_with_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/who-we-work-with');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Who we work with', $crawler->filter('.content-header__title')->text());

        $this->assertNotEmpty($crawler->filter('.list-heading:contains("Memberships") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.list-heading:contains("Service providers") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.list-heading:contains("Content availability and archiving") + .grid-listing > .grid-listing-item'));
        $this->assertNotEmpty($crawler->filter('.list-heading:contains("Committees and initiatives") + .grid-listing > .grid-listing-item'));

        $companyUrl = $crawler->filter('.image-link__link');
        $companyName = $crawler->filter('.image-link__img');

        $this->assertCount(43, $companyUrl);
        $this->assertCount(43, $companyName);

        $this->assertSame(
            [
                'http://www.alpsp.org/',
                'http://publicationethics.org/',
                'https://www.crossref.org/',
                'https://www.niso.org/',
                'https://oaspa.org/',
                'https://orcid.org/',
                'https://aws.amazon.com/',
                'https://browserstack.com/',
                'https://digirati.com/',
                'http://editorialoffice.co.uk/',
                'https://www.ejournalpress.com/',
                'http://www.exeterpremedia.com/',
                'https://www.fastly.com/',
                'https://github.com/',
                'https://glencoesoftware.com/',
                'https://hypothes.is/',
                'https://www.loggly.com/',
                'https://www.thenakedscientists.com/',
                'https://newrelic.com/',
                'https://publons.com/',
                'https://slack.com/',
                'https://www.clockss.org/',
                'https://www.cnki.net/',
                'https://europepmc.org/',
                'http://gooa.las.ac.cn/',
                'https://pubrouter.jisc.ac.uk/',
                'https://www.lockss.org/',
                'https://www.mendeley.com/',
                'http://paperity.org/',
                'https://www.ncbi.nlm.nih.gov/pmc/',
                'https://pubmed.ncbi.nlm.nih.gov/',
                'http://www.share-research.org/',
                'http://www.alba.network/declaration/',
                'https://c4disc.org/',
                'https://www.crossref.org/',
                'https://sfdora.org/',
                'https://doaj.org/',
                'https://www.force11.org/about/directors-and-advisors',
                'https://i4oc.org/',
                'http://jats4r.org/',
                'https://oaspa.org/',
                'https://www.reviewcommons.org/',
                'https://www.rsc.org/new-perspectives/talent/joint-commitment-for-action-inclusion-and-diversity-in-publishing/',
            ],
            array_map('trim', $companyUrl->extract(['href']))
        );

        $this->assertSame(
            [
                'The Association of Learned & Professional Society Publishers',
                'Committee on Publication Ethics',
                'Crossref',
                'Niso',
                'Open Access Scholarly Publishers Association',
                'ORCID',
                'Amazon Web Services',
                'Browserstack',
                'Digirati',
                'Editorial Office Ltd',
                'eJournalPress',
                'Exeter Premedia Services',
                'Fastly',
                'GitHub',
                'Glencoe Software',
                'Hypothesis',
                'Loggly',
                'The Naked Scientists',
                'New Relic',
                'Publons',
                'Slack',
                'CLOCKSS',
                'CNKI',
                'Europe PubMed Central',
                'Go OA',
                'Jisc',
                'LOCKSS',
                'Mendeley',
                'Paperity',
                'PubMed Central',
                'PubMed',
                'SHARE',
                'Alba',
                'c4Disc',
                'Crossref',
                'Declaration on Research Assessment',
                'Directory of Open Access Journals',
                'FORCE11',
                'Initiative for Open Citations',
                'JATS for Reuse',
                'Open Access Scholarly Publishers Association',
                'Review Commons',
                'Royal Society of Chemistry',
            ],
            array_map('trim', $companyName->extract(['alt']))
        );
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('Who we work with | eLife', $crawler->filter('title')->text());
        $this->assertSame('/who-we-work-with', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/who-we-work-with', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('Who we work with', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:description"]'));
        $this->assertEmpty($crawler->filter('meta[name="description"]'));
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

    protected function getUrl() : string
    {
        return '/who-we-work-with';
    }
}
