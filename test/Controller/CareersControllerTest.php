<?php

namespace test\eLife\Journal\Controller;

final class CareersControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_careers_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/careers');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Careers', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/careers';
    }
}
