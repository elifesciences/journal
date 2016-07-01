<?php

namespace test\eLife\Journal\Controller;

final class AboutControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_about_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('About', $crawler->filter('main h1')->text());
    }

    protected function getUrl() : string
    {
        return '/about';
    }
}
