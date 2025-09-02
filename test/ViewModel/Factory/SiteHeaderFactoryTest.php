<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\Journal\ViewModel\Factory\SiteHeaderFactory;
use eLife\Patterns\ViewModel\SiteHeader;
use test\eLife\Journal\KernelTestCase;

final class SiteHeaderFactoryTest extends KernelTestCase
{
    /**
     * @var SiteHeaderFactory
     */
    private $siteHeaderFactory;

    /**
     * @before
     */
    public function createSiteHeaderFactory()
    {
        static::bootKernel();

        $this->siteHeaderFactory = static::$kernel->getContainer()->get('elife.journal.view_model.factory.site_header');
    }

    /**
     * @test
     */
    public function it_returns_a_site_header()
    {
        $this->assertInstanceOf(SiteHeader::class, $this->siteHeaderFactory->createSiteHeader());
    }

    /**
     * @test
     */
    public function it_links_directly_to_the_submit_site()
    {
        $siteHeader = $this->siteHeaderFactory->createSiteHeader();

        $this->assertSame(
            '/submit-your-research',
            $siteHeader['secondaryLinks']['linkedItems'][2]['button']['path']
        );
    }

    /**
     * @test
     */
    public function it_may_be_set_to_home_page()
    {
        $defaultSiteHeader = $this->siteHeaderFactory->createSiteHeader(null);
        $this->assertArrayNotHasKey('isHomePage', $defaultSiteHeader['title']);

        $notHomePageSiteHeader = $this->siteHeaderFactory->createSiteHeader(null, false);
        $this->assertArrayNotHasKey('isHomePage', $notHomePageSiteHeader['title']);

        $homePageSiteHeader = $this->siteHeaderFactory->createSiteHeader(null, true);
        $this->markTestIncomplete();
        $this->assertTrue($homePageSiteHeader['title']['isHomePage']);
    }
}
