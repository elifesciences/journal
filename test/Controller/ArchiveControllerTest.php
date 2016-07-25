<?php

namespace test\eLife\Journal\Controller;

final class ArchiveControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_archive_page_for_a_year()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archive/'.(date('Y') - 1));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Monthly archive', $crawler->filter('.content-header__title')->text());
        $this->assertEquals(date('Y') - 1, $crawler->filter('.content-header__cta option[selected]')->text());
    }

    /**
     * @test
     */
    public function navigate_between_years()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archive/'.(date('Y') - 1));

        $selectNav = $crawler->selectButton('Go')->form();
        $selectNav['year']->setValue(2012);

        $client->submit($selectNav);

        $this->assertSame('/archive', $client->getRequest()->getPathInfo());
        $this->assertSame('year=2012', $client->getRequest()->getQueryString());

        $crawler = $client->followRedirect();

        $this->assertSame('/archive/2012', $client->getRequest()->getPathInfo());
        $this->assertEquals(2012, $crawler->filter('.content-header__cta option[selected]')->text());
    }

    /**
     * @test
     * @dataProvider invalidYearProvider
     */
    public function it_returns_a_404_for_an_invalid_year(int $year)
    {
        $client = static::createClient();

        $client->request('GET', '/archive/'.$year);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function invalidYearProvider() : array
    {
        return [
            'before eLife' => [2011],
            'current year' => [(int) date('Y')],
            'next year' => [date('Y') + 1],
        ];
    }

    /**
     * @test
     */
    public function it_returns_a_404_for_the_index_page_without_a_year()
    {
        $client = static::createClient();

        $client->request('GET', '/archive/');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @dataProvider invalidYearProvider
     */
    public function it_returns_a_404_for_the_index_page_with_an_invalid_year(int $year)
    {
        $client = static::createClient();

        $client->request('GET', '/archive?year='.$year);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        return '/archive/'.(date('Y') - 1);
    }
}
