<?php

namespace test\eLife\Journal\ViewModel;

use eLife\Journal\ViewModel\SiteHeaderFactory;
use eLife\Patterns\ViewModel\SiteHeader;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
    public function it_returns_a_promise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->siteHeaderFactory->createSiteHeader());
    }

    /**
     * @test
     * @depends it_returns_a_promise
     */
    public function it_returns_a_site_header()
    {
        $footer = $this->siteHeaderFactory->createSiteHeader()->wait();

        $this->assertInstanceOf(SiteHeader::class, $footer);
    }
}
