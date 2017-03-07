<?php

namespace test\eLife\Journal\Controller;

final class AnnualReportsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_annual_reports_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/annual-reports');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Annual reports', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/annual-reports';
    }
}
