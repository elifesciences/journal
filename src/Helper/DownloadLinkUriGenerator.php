<?php

namespace eLife\Journal\Helper;

use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

final class DownloadLinkUriGenerator
{
    private $urlGenerator;
    private $uriSigner;

    public function __construct(UrlGeneratorInterface $urlGenerator, UriSigner $uriSigner)
    {
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
    }

    public function generate(DownloadLink $link) : string
    {
        $uri = $this->urlGenerator->generate('download', ['uri' => base64_encode($link->getUri()), 'name' => $link->getFilename()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->uriSigner->sign($uri);
    }

    public function check(string $uri) : DownloadLink
    {
        if (!$this->uriSigner->check($uri)) {
            throw new UnexpectedValueException('Not a valid signed URI');
        }

        $path = explode('/', parse_url($uri, PHP_URL_PATH));

        $uri = array_pop($path);

        if ('download' !== $second = array_pop($path)) {
            $name = $uri;
            $uri = $second;
        }

        return new DownloadLink(base64_decode($uri), $name ?? null);
    }
}
