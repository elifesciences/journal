<?php

namespace test\eLife\Journal\Controller;

final class SubjectsControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_subjects_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Browse our research categories', $crawler->filter('.content-header__title')->text());
    }

    protected function getUrl() : string
    {
        return '/subjects';
    }
}
