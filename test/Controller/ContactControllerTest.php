<?php

namespace test\eLife\Journal\Controller;

final class ContactControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_contact_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/contact');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Contact', $crawler->filter('h1')->text());
    }

    protected function getUrl() : string
    {
        return '/contact';
    }
}
