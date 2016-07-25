<?php

namespace test\eLife\Journal\Controller;

final class AlertsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_alerts_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/alerts');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Alerts', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/alerts';
    }
}
