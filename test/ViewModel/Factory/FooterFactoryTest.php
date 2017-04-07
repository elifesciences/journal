<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\Journal\ViewModel\Factory\FooterFactory;
use eLife\Patterns\ViewModel\Footer;
use Symfony\Bridge\PhpUnit\ClockMock;
use test\eLife\Journal\KernelTestCase;
use Traversable;

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

    /**
     * @test
     * @dataProvider yearProvider
     * @group        time-sensitive
     */
    public function it_includes_a_link_to_the_latest_year_in_the_archive(string $today, string $expected)
    {
        ClockMock::withClockMock(strtotime($today));

        $footer = $this->footerFactory->createFooter();

        $link = null;

        foreach ($footer['footerMenuLinks'] as $footerMenuLink) {
            if ('Monthly archive' === $footerMenuLink['name']) {
                $link = $footerMenuLink['url'];
                break;
            }
        }

        $this->assertSame($expected, $link);
    }

    public function yearProvider() : Traversable
    {
        yield 'in January 2017' => ['2017-01-01T00:00:00Z', '/archive/2016'];
        yield 'in February 2017' => ['2017-02-01T00:00:00Z', '/archive/2017'];
    }
}
