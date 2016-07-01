<?php

namespace test\eLife\Journal\Controller;

final class PressPacksControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_press_packs_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/for-the-press');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('For the press', $crawler->filter('h1')->text());
    }

    protected function getUrl() : string
    {
        return '/for-the-press';
    }
}
