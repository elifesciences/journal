<?php

namespace eLife\Journal\Helper;

use Countable;
use Pagerfanta\Pagerfanta;

final class Paginator implements Countable
{
    private $title;
    private $pagerfanta;
    private $uriGenerator;

    public function __construct(string $title, Pagerfanta $pagerfanta, callable $uriGenerator)
    {
        $this->title = $title;
        $this->pagerfanta = $pagerfanta;
        $this->uriGenerator = $uriGenerator;
    }

    public function count() : int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getTotal() : int
    {
        return $this->pagerfanta->getNbResults();
    }

    public function getCurrentPage() : int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    public function getCurrentPagePath() : string
    {
        if (1 === $this->getCurrentPage()) {
            return $this->getFirstPagePath();
        }

        return call_user_func($this->uriGenerator, $this->getCurrentPage());
    }

    public function getFirstPagePath() : string
    {
        return call_user_func($this->uriGenerator, null);
    }

    /**
     * @return int|null
     */
    public function getPreviousPage()
    {
        if (!$this->pagerfanta->hasPreviousPage()) {
            return null;
        }

        return $this->pagerfanta->getPreviousPage();
    }

    /**
     * @return string|null
     */
    public function getPreviousPagePath()
    {
        if (!$this->pagerfanta->hasPreviousPage()) {
            return null;
        } elseif (1 === $this->pagerfanta->getPreviousPage()) {
            return $this->getFirstPagePath();
        }

        return call_user_func($this->uriGenerator, $this->pagerfanta->getPreviousPage());
    }

    /**
     * @return int|null
     */
    public function getNextPage()
    {
        if (!$this->pagerfanta->hasNextPage()) {
            return null;
        }

        return $this->pagerfanta->getNextPage();
    }

    /**
     * @return string|null
     */
    public function getNextPagePath()
    {
        if (!$this->pagerfanta->hasNextPage()) {
            return null;
        }

        return call_user_func($this->uriGenerator, $this->pagerfanta->getNextPage());
    }

    public function getLastPage() : int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getLastPagePath() : string
    {
        return call_user_func($this->uriGenerator, $this->pagerfanta->getNbPages());
    }

    public function getItems() : array
    {
        return iterator_to_array($this->pagerfanta);
    }
}
