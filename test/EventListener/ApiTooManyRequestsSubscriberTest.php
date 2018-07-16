<?php

namespace test\eLife\Journal\EventListener;

use eLife\ApiClient\Exception\BadResponse;
use eLife\Journal\EventListener\ApiTooManyRequestsSubscriber;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ApiTooManyRequestsSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_turns_a_too_many_requests_response_from_the_api_into_a_429_exception()
    {
        $subscriber = new ApiTooManyRequestsSubscriber();

        $exception = new BadResponse('Too Many Requests', new GuzzleRequest('GET', 'http://www.example.com/'), new GuzzleResponse(429));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $actual = $event->getException();

        $this->assertInstanceOf(HttpException::class, $actual);
        $this->assertSame(429, $actual->getStatusCode());
        $this->assertSame('Too Many Requests', $actual->getMessage());
        $this->assertSame($exception, $actual->getPrevious());
        $this->assertArraySubset(['Retry-After' => 60], $actual->getHeaders());
    }

    /**
     * @test
     */
    public function it_ignores_other_responses()
    {
        $subscriber = new ApiTooManyRequestsSubscriber();

        $exception = new BadResponse('Timeout', new GuzzleRequest('GET', 'http://www.example.com/'), new GuzzleResponse(400));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }
}
