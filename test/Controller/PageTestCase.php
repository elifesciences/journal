<?php

namespace test\eLife\Journal\Controller;

use test\eLife\Journal\WebTestCase;

abstract class PageTestCase extends WebTestCase
{
    /**
     * @test
     */
    final public function it_has_the_header()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $header = $crawler->filter('header');

        $this->assertCount(1, $header);
        $this->assertContains('site-header', $header->attr('class'));
    }

    /**
     * @test
     */
    final public function it_has_the_footer()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $footer = $crawler->filter('footer');

        $this->assertCount(1, $footer);
        $this->assertContains('site-footer', $footer->attr('class'));
    }

    abstract protected function getUrl() : string;
}
