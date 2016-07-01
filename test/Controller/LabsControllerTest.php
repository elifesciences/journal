<?php

namespace test\eLife\Journal\Controller;

final class LabsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_labs_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/labs');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Labs', $crawler->filter('h1')->text());
    }

    protected function getUrl() : string
    {
        return '/labs';
    }
}
