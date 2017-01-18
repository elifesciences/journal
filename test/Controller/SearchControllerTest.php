<?php

namespace test\eLife\Journal\Controller;

final class SearchControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_the_search_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Search', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_has_a_search_box_in_the_header()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $form = $crawler->selectButton('Search')->form();

        $form['for'] = 'keyword';

        $client->submit($form);

        $this->assertSame('/search?for=keyword', $client->getRequest()->getRequestUri());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        return '/search';
    }
}
