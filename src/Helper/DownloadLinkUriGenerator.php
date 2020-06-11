<?php

namespace eLife\Journal\Helper;

use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

final class DownloadLinkUriGenerator
{
    const URL_UNSAFE_CHARS = '+/=';
    const URL_UNSAFE_REPLACES = '._-';
    const WRAP_REL_CANONICAL = '<relCanonical>';

    private $urlGenerator;
    private $uriSigner;

    public function __construct(UrlGeneratorInterface $urlGenerator, UriSigner $uriSigner)
    {
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
    }

    public function generate(DownloadLink $link) : string
    {
        $downloadUri = $link->getUri();

        if ($link->getRelCanonical()) {
            $downloadUri .= self::WRAP_REL_CANONICAL.$link->getRelCanonical();
        }

        $uri = $this->urlGenerator->generate('download', ['uri' => strtr(base64_encode($downloadUri), self::URL_UNSAFE_CHARS, self::URL_UNSAFE_REPLACES), 'name' => $link->getFilename()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->uriSigner->sign($uri);
    }

    public function check(string $uri) : DownloadLink
    {
        if (!$this->uriSigner->check($uri)) {
            throw new UnexpectedValueException('Not a valid signed URI');
        }

        $path = explode('/', parse_url($uri, PHP_URL_PATH));

        $name = array_pop($path);
        list($uri, $relCanonical) = explode(self::WRAP_REL_CANONICAL, base64_decode(strtr(array_pop($path), self::URL_UNSAFE_REPLACES, self::URL_UNSAFE_CHARS)).self::WRAP_REL_CANONICAL);

        return new DownloadLink(base64_decode(strtr($uri, self::URL_UNSAFE_REPLACES, self::URL_UNSAFE_CHARS)), $name, $relCanonical ?? null);
    }
}
