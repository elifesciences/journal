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
        $this->assertSame((date('Y') - 1).' archive', $crawler->filter('main h1')->text());
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

    protected function getUrl() : string
    {
        return '/archive/'.(date('Y') - 1);
    }
}
