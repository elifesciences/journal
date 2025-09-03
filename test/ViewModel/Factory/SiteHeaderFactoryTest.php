<?php

namespace test\eLife\Journal\ViewModel\Factory;

use eLife\Journal\ViewModel\Factory\SiteHeaderFactory;
use eLife\Patterns\ViewModel\SiteHeader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SiteHeaderFactoryTest extends TestCase
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var SiteHeaderFactory
     */
    private $siteHeaderFactory;

    /**
     * @before
     */
    public function createSiteHeaderFactory()
    {
        $request = $this->createMock(Request::class);
        $request->method('get')->with('_route')->willReturn('/');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->urlGenerator->method('generate')
            ->willReturnCallback(function ($route) {
                return $route === 'submit-your-research' ? '/submit-your-research' : 'foo';
            });

        $this->siteHeaderFactory = new SiteHeaderFactory($this->urlGenerator, $requestStack);
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
        $this->assertTrue($homePageSiteHeader['title']['isHomePage']);
    }

    /**
     * @test
     */
    public function it_will_not_show_the_search_box_on_the_search_page()
    {
        $notSearchPageSiteHeader = $this->siteHeaderFactory->createSiteHeader();
        $this->assertNotNull($notSearchPageSiteHeader['searchBox']);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('_route')->willReturn('search');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $searchPageSiteHeader = (new SiteHeaderFactory($this->urlGenerator, $requestStack))->createSiteHeader();
        $this->assertNull($searchPageSiteHeader['searchBox']);
    }
}
