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

        $this->assertSame(
            [
                [
                    'http://www.alpsp.org/',
                    'The Association of Learned & Professional Society Publishers',
                ],
                [
                    'http://publicationethics.org/',
                    'Committee on Publication Ethics',
                ],
                [
                    'https://www.crossref.org/',
                    'Crossref',
                ],
                [
                    'https://www.niso.org/',
                    'Niso',
                ],
                [
                    'https://oaspa.org/',
                    'Open Access Scholarly Publishers Association',
                ],
                [
                    'https://orcid.org/',
                    'ORCID',
                ],
                [
                    'https://aws.amazon.com/',
                    'Amazon Web Services',
                ],
                [
                    'https://browserstack.com/',
                    'Browserstack',
                ],
                [
                    'https://digirati.com/',
                    'Digirati',
                ],
                [
                    'http://editorialoffice.co.uk/',
                    'Editorial Office Ltd',
                ],
                [
                    'https://www.ejournalpress.com/',
                    'eJournalPress',
                ],
                [
                    'https://www.kriyadocs.com/',
                    'Exeter Premedia Services',
                ],
                [
                    'https://www.fastly.com/',
                    'Fastly',
                ],
                [
                    'https://github.com/',
                    'GitHub',
                ],
                [
                    'https://glencoesoftware.com/',
                    'Glencoe Software',
                ],
                [
                    'https://hypothes.is/',
                    'Hypothesis',
                ],
                [
                    'https://www.loggly.com/',
                    'Loggly',
                ],
                [
                    'https://www.thenakedscientists.com/',
                    'The Naked Scientists',
                ],
                [
                    'https://publons.com/',
                    'Publons',
                ],
                [
                    'https://slack.com/',
                    'Slack',
                ],
                [
                    'https://www.clockss.org/',
                    'CLOCKSS',
                ],
                [
                    'https://www.cnki.net/',
                    'CNKI',
                ],
                [
                    'https://doaj.org/toc/2050-084X/',
                    'DOAJ Seal',
                ],
                [
                    'https://europepmc.org/',
                    'Europe PubMed Central',
                ],
                [
                    'http://gooa.las.ac.cn/',
                    'Go OA',
                ],
                [
                    'https://pubrouter.jisc.ac.uk/',
                    'Jisc',
                ],
                [
                    'https://www.lockss.org/',
                    'LOCKSS',
                ],
                [
                    'https://www.mendeley.com/',
                    'Mendeley',
                ],
                [
                    'http://paperity.org/',
                    'Paperity',
                ],
                [
                    'https://www.ncbi.nlm.nih.gov/pmc/',
                    'PubMed Central',
                ],
                [
                    'https://pubmed.ncbi.nlm.nih.gov/',
                    'PubMed',
                ],
                [
                    'http://www.share-research.org/',
                    'SHARE',
                ],
                [
                    'http://www.alba.network/declaration/',
                    'Alba',
                ],
                [
                    'https://c4disc.org/',
                    'c4Disc',
                ],
                [
                    'https://www.crossref.org/',
                    'Crossref',
                ],
                [
                    'https://sfdora.org/',
                    'Declaration on Research Assessment',
                ],
                [
                    'https://doaj.org/',
                    'Directory of Open Access Journals',
                ],
                [
                    'https://www.force11.org/about/directors-and-advisors',
                    'FORCE11',
                ],
                [
                    'https://i4oc.org/',
                    'Initiative for Open Citations',
                ],
                [
                    'https://jats4r.niso.org/',
                    'JATS for Reuse',
                ],
                [
                    'https://oaspa.org/',
                    'Open Access Scholarly Publishers Association',
                ],
                [
                    'https://www.reviewcommons.org/',
                    'Review Commons',
                ],
                [
                    'https://www.rsc.org/new-perspectives/talent/joint-commitment-for-action-inclusion-and-diversity-in-publishing/',
                    'Royal Society of Chemistry',
                ],
            ],
            $crawler->filter('.grid-listing-item--image-link')
                ->each(function ($companyLink) {
                    return [
                        $companyLink->filter('.image-link__link')->attr('href'),
                        $companyLink->filter('.image-link__img')->attr('alt'),
                    ];
                })
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
