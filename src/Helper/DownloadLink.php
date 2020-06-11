<?php

namespace eLife\Journal\Helper;

final class DownloadLink
{
    private $uri;
    private $filename;
    private $relCanonical;

    public function __construct(string $uri, string $filename, string $relCanonical = null)
    {
        $this->uri = $uri;
        $this->filename = $filename;
        $this->relCanonical = $relCanonical;
    }

    public static function fromUri(string $uri, string $relCanonical = null)
    {
        return new self($uri, basename($uri), $relCanonical);
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * @return string|null
     */
    public function getRelCanonical()
    {
        return $this->relCanonical;
    }
}
