<?php

namespace eLife\Journal\Helper;

use DateInterval;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\UriSigner;

/**
 * UriSigner::sign()/check() call private computeHash()/doVerify() methods, so subclassing
 * and overriding just the hashing cannot change their behaviour (private methods are not
 * polymorphic in PHP). This class instead reimplements sign()/check() in full, using the
 * padded, standard-base64 HMAC format that Symfony\Component\HttpKernel\UriSigner used prior
 * to the symfony/http-foundation 6.4 move, which silently switched to an unpadded, URL-safe
 * encoding (strtr + rtrim).
 *
 * The journal-downloads service verifies download link signatures independently of this app
 * and still expects the old format, so download links must keep being signed this way.
 *
 * @see https://github.com/elifesciences/journal-downloads/blob/master/src/signer.ts
 */
final class LegacyUriSigner extends UriSigner
{
    private $secret;
    private $parameter;

    public function __construct(string $secret, string $parameter = '_hash')
    {
        parent::__construct($secret, $parameter);

        $this->secret = $secret;
        $this->parameter = $parameter;
    }

    public function sign(string $uri, DateTimeInterface|DateInterval|int|null $expiration = null) : string
    {
        $url = parse_url($uri);
        $params = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        }

        $uri = $this->buildUrl($url, $params);
        $params[$this->parameter] = $this->computeHash($uri);

        return $this->buildUrl($url, $params);
    }

    public function check(string $uri) : bool
    {
        $url = parse_url($uri);
        $params = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        }

        if (empty($params[$this->parameter])) {
            return false;
        }

        $hash = $params[$this->parameter];
        unset($params[$this->parameter]);

        return hash_equals($this->computeHash($this->buildUrl($url, $params)), $hash);
    }

    private function computeHash(string $uri) : string
    {
        return base64_encode(hash_hmac('sha256', $uri, $this->secret, true));
    }

    private function buildUrl(array $url, array $params = []) : string
    {
        ksort($params, SORT_STRING);
        $url['query'] = http_build_query($params, '', '&');

        $scheme = isset($url['scheme']) ? $url['scheme'].'://' : '';
        $host = $url['host'] ?? '';
        $port = isset($url['port']) ? ':'.$url['port'] : '';
        $user = $url['user'] ?? '';
        $pass = isset($url['pass']) ? ':'.$url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $url['path'] ?? '';
        $query = ($url['query'] ?? '') !== '' ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';

        return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
    }
}
