<?php

namespace test\eLife\Journal\Controller;

final class WhoWeWorkWithControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_who_we_work_with_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/who-we-work-with');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Who we work with', $crawler->filter('h1')->text());
    }

    protected function getUrl() : string
    {
        return '/who-we-work-with';
    }
}
