<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

final class StatusTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_200_when_the_application_is_correctly_setup()
    {
        $client = static::createClient();

        $client->request('GET', '/status');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
