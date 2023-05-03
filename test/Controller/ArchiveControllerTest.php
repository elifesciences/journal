<?php

namespace test\eLife\Journal\Controller;

use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group time-sensitive
 */
final class ArchiveControllerTest extends PageTestCase
{
    public function setUp()
    {
        ClockMock::withClockMock(strtotime('2016-01-01T00:00:00Z'));
    }

    /**
     * @test
     */
    public function it_displays_the_archive_page_for_a_year()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archive/2015');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Monthly archive', $crawler->filter('.content-header__title')->text());
        $this->assertEquals(2015, $crawler->filter('.content-header__cta option[selected]')->text());
    }

    /**
     * @test
     */
    public function navigate_between_years()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archive/2015');

        $selectNav = $crawler->selectButton('Go')->form();
        $selectNav['year']->setValue(2012);

        $client->submit($selectNav);

        $this->assertSame('/archive', $client->getRequest()->getPathInfo());
        $this->assertSame('go=&year=2012', $client->getRequest()->getQueryString());

        $crawler = $client->followRedirect();

        $this->assertSame('/archive/2012', $client->getRequest()->getPathInfo());
        $this->assertEquals(2012, $crawler->filter('.content-header__cta option[selected]')->text());
    }

    /**
     * @test
     */
    public function it_has_metadata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl().'?foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('2015 | eLife', $crawler->filter('title')->text());
        $this->assertSame('/archive/2015', $crawler->filter('link[rel="canonical"]')->attr('href'));
        $this->assertSame('http://localhost/archive/2015', $crawler->filter('meta[property="og:url"]')->attr('content'));
        $this->assertSame('2015', $crawler->filter('meta[property="og:title"]')->attr('content'));
        $this->assertEmpty($crawler->filter('meta[property="og:description"]'));
        $this->assertEmpty($crawler->filter('meta[name="description"]'));
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

    /**
     * @test
     * @dataProvider invalidYearProvider
     */
    public function it_returns_a_404_for_an_invalid_year($year)
    {
        $client = static::createClient();

        $client->request('GET', '/archive/'.$year);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidYearProvider() : array
    {
        return [
            'before eLife' => [2011],
            'current year' => [2016],
            'next year' => [2017],
            'not a year' => ['foo'],
        ];
    }

    /**
     * @test
     */
    public function it_returns_a_404_for_the_index_page_without_a_year()
    {
        $client = static::createClient();

        $client->request('GET', '/archive');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @dataProvider invalidYearProvider
     */
    public function it_returns_a_404_for_the_index_page_with_an_invalid_year($year)
    {
        $client = static::createClient();

        $client->request('GET', '/archive?year='.$year);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        return '/archive/2015';
    }
}
