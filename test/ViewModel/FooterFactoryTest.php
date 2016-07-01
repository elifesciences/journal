<?php

namespace test\eLife\Journal\ViewModel;

use eLife\Journal\ViewModel\FooterFactory;
use eLife\Patterns\ViewModel\Footer;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FooterFactoryTest extends KernelTestCase
{
    /**
     * @var FooterFactory
     */
    private $footerFactory;

    /**
     * @before
     */
    public function createFooterFactory()
    {
        static::bootKernel();

        $this->footerFactory = static::$kernel->getContainer()->get('elife.journal.view_model.factory.footer');
    }

    /**
     * @test
     */
    public function it_returns_a_promise()
    {
        $this->assertInstanceOf(PromiseInterface::class, $this->footerFactory->createFooter());
    }

    /**
     * @test
     * @depends it_returns_a_promise
     */
    public function it_returns_a_footer()
    {
        $footer = $this->footerFactory->createFooter()->wait();

        $this->assertInstanceOf(Footer::class, $footer);
    }
}
