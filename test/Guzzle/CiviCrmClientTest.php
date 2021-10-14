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
                new Response(200, [], json_encode(['id' => '12345'])),
                new Response(200, [], json_encode(['is_error' => 0])),
        ], $container);

        $subscribe = $client->subscribe('email@example.com', ['latest_articles']);

        $this->assertEquals([
            'contact_id' => '12345',
            'groups_added' => true,
        ], $subscribe->wait());

        $this->assertCount(2, $container);

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
            'entity' => 'GroupContact',
            'action' => 'create',
            'json' => [
                'group_id' => [
                    'All_Content_53',
                ],
                'contact_id' => '12345',
            ],
            'api_key' => 'api-key',
            'key' => 'site-key',
        ]), $secondRequest->getUri()->getQuery());
    }

    /**
     * @test
     */
    public function it_can_handle_error_from_civi()
    {
        $container = [];

        $client = $this->prepareClient([
            $firstError = new Response(200, [], json_encode(['is_error' => 1, 'error_message' => 'Error'])),
            new Response(200, [], json_encode(['id' => '23456'])),
            $secondError = new Response(200, [], json_encode(['is_error' => 1, 'error_message' => 'Error 2'])),
        ], $container);

        try {
            $client->subscribe('email@example.com', ['latest_articles'])->wait();
            $this->fail('CiviCrmResponseError was not thrown');
        } catch (CiviCrmResponseError $e) {
            $this->assertSame('Error', $e->getMessage());
            $this->assertSame($firstError, $e->getResponse());
        }

        try {
            $client->subscribe('email@example.com', ['latest_articles', 'early_career'])->wait();
            $this->fail('CiviCrmResponseError was not thrown');
        } catch (CiviCrmResponseError $e) {
            $this->assertSame('Error 2', $e->getMessage());
            $this->assertSame($secondError, $e->getResponse());
        }
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
