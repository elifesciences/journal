<?php

namespace eLife\Journal\Helper;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait CreatesDownloadUri
{
    use HasUrlGenerator;
    use HasUriSigner;

    final protected function createDownloadUri(string $fileUri, string $name = '') : string
    {
        $uri = $this->getUrlGenerator()->generate('download', ['uri' => base64_encode($fileUri), 'name' => $name], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->getUriSigner()->sign($uri);
    }
}
