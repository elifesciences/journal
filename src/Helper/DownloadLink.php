<?php

namespace eLife\Journal\Helper;

final class DownloadLink
{
    const QUERY_PARAMETER_CANONICAL_URI = 'canonicalUri';

    private $uri;
    private $filename;
    private $canonicalUri = null;

    public function __construct(string $uri, string $filename)
    {
        $this->uri = $this->stripCanonicalUri($uri);
        $this->filename = $filename;
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

    private function stripCanonicalUri(string $uri) : string
    {
        $parseUri = parse_url($uri);
        if (!empty($parseUri['query'])) {
            parse_str($parseUri['query'], $query);
            if (isset($query[self::QUERY_PARAMETER_CANONICAL_URI])) {
                $this->canonicalUri = $query[self::QUERY_PARAMETER_CANONICAL_URI] ?? null;
                unset($query[self::QUERY_PARAMETER_CANONICAL_URI]);
                $uri = "{$parseUri['scheme']}://{$parseUri['host']}{$parseUri['path']}";
                if ($newQuery = http_build_query($query)) {
                    $uri .= "?{$newQuery}";
                }
            }
        }

        return $uri;
    }
}
