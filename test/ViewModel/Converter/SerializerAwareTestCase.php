<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\ApiSdk;
use PHPUnit\Framework\Attributes\Before;

trait SerializerAwareTestCase
{
    private $serializer;

    #[Before]
    public function setUpSerializer(): void
    {
        // in the future: use ForbiddingHttpClient when available
        $httpClient = $this->createMock(HttpClient::class);
        $apiSdk = new ApiSdk($httpClient);
        $this->serializer = $apiSdk->getSerializer();
    }
}
