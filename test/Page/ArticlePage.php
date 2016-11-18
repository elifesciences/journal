<?php

namespace test\eLife\Journal\Page;

final class ArticlePage extends Page
{
    public function citation() : string
    {
        return $this->crawler->filter('.contextual-data__cite_wrapper')->text();
    }

    public function headerAuthor(int $index) : string
    {
        return trim($this->crawler->filter('.content-header__author_list_item')->eq($index)->text());
    }

    public function headerAuthorCount() : int
    {
        return $this->crawler->filter('.content-header__author_list_item')->count();
    }

    public function headerInstitution(int $index) : string
    {
        return trim($this->crawler->filter('.content-header__institution_list_item')->eq($index)->text());
    }

    public function headerInstitutionCount() : int
    {
        return $this->crawler->filter('.content-header__institution_list_item')->count();
    }
}
