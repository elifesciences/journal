<?php

namespace eLife\Journal\Helper;

final class DownloadLink
{
    const QUERY_PARAMETER_CANONICAL_URI = 'canonicalUri';

    private $uri;
    private $filename;
    private $canonicalUri;

    public function __construct(string $uri, string $filename)
    {
        $this->uri = $uri;
        $this->filename = $filename;
        $this->canonicalUri = $this->findCanonicalUri($uri);
    }

    public static function fromUri(string $uri)
    {
        return new self($uri, basename(explode('?', $uri)[0]));
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
    public function getCanonicalUri()
    {
        return $this->canonicalUri;
    }

    /**
     * @return string|null
     */
    private function findCanonicalUri(string $uri)
    {
        $parseUri = parse_url($uri);
        if (!empty($parseUri['query'])) {
            parse_str($parseUri['query'], $query);
            if (isset($query[self::QUERY_PARAMETER_CANONICAL_URI])) {
                return $query[self::QUERY_PARAMETER_CANONICAL_URI] ?? null;
            }
        }

        return null;
    }
}
