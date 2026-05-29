<?php

namespace test\eLife\Journal;

use eLife\ApiValidator\MessageValidator;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class MockApiHttpClient implements HttpClientInterface
{
    private $storagePath;
    private $baseUri;
    private $headersBlacklist;
    private $validator;

    public function __construct(string $storagePath, string $baseUri = '', array $headersBlacklist = [], MessageValidator $validator = null)
    {
        $this->storagePath = $storagePath;
        $this->baseUri = $baseUri;
        $this->headersBlacklist = array_map('strtolower', $headersBlacklist);
        $this->validator = $validator;
    }

    public function request(string $method, string $url, array $options = []): SymfonyResponseInterface
    {
        if ($this->baseUri !== '' && parse_url($url, PHP_URL_SCHEME) === null) {
            $url = rtrim($this->baseUri, '/').'/'.ltrim($url, '/');
        }

        $filename = $this->buildFilename($method, $url, $options['headers'] ?? []);

        if (!file_exists($filename)) {
            $mock = new MockResponse('', ['http_code' => 404]);
        } else {
            $data = json_decode(file_get_contents($filename), true);
            $mock = new MockResponse(base64_decode($data['body']), [
                'http_code' => $data['status'],
                'response_headers' => $data['headers'],
            ]);
        }

        return (new MockHttpClient($mock))->request($method, $url, $options);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return (new MockHttpClient())->stream($responses, $timeout);
    }

    public function save(RequestInterface $request, ResponseInterface $response): void
    {
        if ($this->validator) {
            $this->validator->validate($request);
            $this->validator->validate($response);
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = $values;
        }

        $body = (string) $response->getBody();
        $response->getBody()->rewind();

        $data = [
            'status' => $response->getStatusCode(),
            'headers' => $headers,
            'body' => base64_encode($body),
        ];

        $requestHeaders = array_map(
            function ($values) { return implode(', ', $values); },
            $request->getHeaders()
        );

        $filename = $this->buildFilename(
            $request->getMethod(),
            (string) $request->getUri(),
            $requestHeaders
        );

        $fs = new Filesystem();
        $fs->mkdir(dirname($filename));
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function buildFilename(string $method, string $url, array $headers): string
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        $rawPath = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? '';
        $scheme = $parsed['scheme'] ?? 'https';
        $port = $parsed['port'] ?? null;
        $userInfo = ($parsed['user'] ?? '').(isset($parsed['pass']) ? ':'.$parsed['pass'] : '');

        $fingerprint = $this->fingerprint($method, $scheme, $rawPath, $query, $port, $userInfo, $headers);

        $decodedPath = urldecode(ltrim($rawPath, '/'));
        $dirPart = $host.($decodedPath !== '' ? '/'.$decodedPath : '');

        return $this->storagePath.'/'.$dirPart.'/'.strtoupper($method).'_'.$fingerprint.'.json';
    }

    private function fingerprint(string $method, string $scheme, string $path, string $query, ?int $port, string $userInfo, array $headers): string
    {
        if ($query !== '') {
            $params = Query::parse($query);
            ksort($params);
            array_walk($params, function (&$value) {
                if (is_array($value)) {
                    sort($value);
                }
            });
            $query = Query::build($params);
        }

        $normalizedHeaders = array_map('strval', array_diff_key(
            array_change_key_case($headers, CASE_LOWER),
            array_flip($this->headersBlacklist)
        ));

        return md5(serialize([
            'method' => strtoupper($method),
            'path' => $path,
            'query' => $query,
            'user_info' => $userInfo,
            'port' => $port,
            'scheme' => $scheme,
            'headers' => $normalizedHeaders,
        ]));
    }
}
