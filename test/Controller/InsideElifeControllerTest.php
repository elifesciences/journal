<?php

namespace test\eLife\Journal\Controller;

final class InsideElifeControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_inside_elife_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/inside-elife');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Inside eLife', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/inside-elife';
    }
}
