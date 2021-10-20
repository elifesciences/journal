<?php

namespace test\eLife\Journal\Guzzle;

use eLife\Journal\Exception\CiviCrmResponseError;
use eLife\Journal\Guzzle\CiviCrmClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class CiviCrmClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_subscribe_a_user()
    {
        $container = [];

        $client = $this->prepareClient([
            new Response(200, [], json_encode(['id' => '11111'])),
            new Response(200, [], json_encode(['is_error' => 0])),
            new Response(200, [], json_encode(['is_error' => 0])),
        ], $container);

        $subscribe = $client->subscribe('email@example.com', ['latest_articles', 'elife_newsletter']);

        $this->assertEquals([
            'contact_id' => '11111',
            'email' => 'email@example.com',
            'subscribe' => [
                53 => true,
                1032 => true,
            ],
        ], $subscribe->wait());

        $this->assertCount(3, $container);

        /** @var Request $firstRequest */
        $firstRequest = $container[0]['request'];
        $this->assertEquals('POST', $firstRequest->getMethod());
        $this->assertSame($this->prepareQuery([
            'entity' => 'Contact',
            'action' => 'create',
            'json' => [
                'contact_type' => 'Individual',
                'email' => 'email@example.com',
            ],
            'api_key' => 'api-key',
            'key' => 'site-key',
        ]), $firstRequest->getUri()->getQuery());

        /** @var Request $secondRequest */
        $secondRequest = $container[1]['request'];
        $this->assertEquals('POST', $secondRequest->getMethod());
        $this->assertSame($this->prepareQuery([
            'entity' => 'MailingEventSubscribe',
            'action' => 'create',
            'json' => [
                'email' => 'email@example.com',
                'contact_id' => '11111',
                'group_id' => 53,
            ],
            'api_key' => 'api-key',
            'key' => 'site-key',
        ]), $secondRequest->getUri()->getQuery());

        /** @var Request $thirdRequest */
        $thirdRequest = $container[2]['request'];
        $this->assertEquals('POST', $thirdRequest->getMethod());
        $this->assertSame($this->prepareQuery([
            'entity' => 'MailingEventSubscribe',
            'action' => 'create',
            'json' => [
                'email' => 'email@example.com',
                'contact_id' => '11111',
                'group_id' => 1032,
            ],
            'api_key' => 'api-key',
            'key' => 'site-key',
        ]), $thirdRequest->getUri()->getQuery());
    }

    /**
     * @test
     */
    public function it_can_handle_error_from_civi()
    {
        $container = [];

        $client = $this->prepareClient([
            $firstError = new Response(200, [], json_encode(['is_error' => 1, 'error_message' => 'Error'])),
            new Response(200, [], json_encode(['id' => '22222'])),
            new Response(200, [], json_encode(['is_error' => 0])),
            $secondError = new Response(200, [], json_encode(['is_error' => 1, 'error_message' => 'Error 2'])),
            new Response(200, [], json_encode(['is_error' => 0])),
        ], $container);

        try {
            $client->subscribe('email@example.com', ['latest_articles'])->wait();
            $this->fail('CiviCrmResponseError was not thrown');
        } catch (CiviCrmResponseError $e) {
            $this->assertSame('Error', $e->getMessage());
            $this->assertSame($firstError, $e->getResponse());
        }

        try {
            $client->subscribe('email@example.com', ['latest_articles', 'early_career', 'technology'])->wait();
            $this->fail('CiviCrmResponseError was not thrown');
        } catch (CiviCrmResponseError $e) {
            $this->assertSame('Error 2', $e->getMessage());
            $this->assertSame($secondError, $e->getResponse());
        }

        $this->assertCount(5, $container);
    }

    private function prepareClient(array $queue = [], array &$container = []) : CiviCrmClient
    {
        $history = Middleware::history($container);

        $mock = new MockHandler($queue);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new CiviCrmClient(new Client(['handler' => $handlerStack]), 'api-key', 'site-key');
    }

    private function prepareQuery(array $query) : string
    {
        return http_build_query(array_map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }, $query));
    }
}
