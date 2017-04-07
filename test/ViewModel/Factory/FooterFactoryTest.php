<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\Journal\ViewModel\Factory\FooterFactory;
use eLife\Patterns\ViewModel\Footer;
use test\eLife\Journal\KernelTestCase;

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
    public function it_returns_a_footer()
    {
        $this->assertInstanceOf(Footer::class, $this->footerFactory->createFooter());
    }
}
