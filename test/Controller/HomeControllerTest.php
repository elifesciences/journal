<?php

namespace test\eLife\Journal\Controller;

final class HomeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_homepage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('eLife', $crawler->filter('main h1')->text());
    }

    protected function getUrl() : string
    {
        return '/';
    }
}
