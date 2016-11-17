<?php

namespace test\eLife\Journal\Page;

use Symfony\Component\DomCrawler\Crawler;

class Page
{
    protected $crawler;
    
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function headerTitle() : string
    {
        return $this->crawler->filter('.content-header__title')->text();
    }
}
