<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\ApiSdk;

trait SerializerAwareTestCase
{
    private $serializer;

    /**
     * @before
     */
    public function setUpSerializer()
    {
        // in the future: use ForbiddingHttpClient when available
        $httpClient = $this->createMock(HttpClient::class);
        $apiSdk = new ApiSdk($httpClient);
        $this->serializer = $apiSdk->getSerializer();
    }
}
