<?php

namespace eLife\Journal\Helper;

final class DownloadLink
{
    private $uri;
    private $filename;

    public function __construct(string $uri, string $filename = null)
    {
        $this->uri = $uri;
        $this->filename = $filename;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
