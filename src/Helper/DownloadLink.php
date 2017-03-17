<?php

namespace eLife\Journal\Helper;

final class DownloadLink
{
    private $uri;
    private $filename;

    public function __construct(string $uri, string $filename)
    {
        $this->uri = $uri;
        $this->filename = $filename;
    }

    public static function fromUri(string $uri)
    {
        return new self($uri, basename($uri));
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getFilename() : string
    {
        return $this->filename;
    }
}
