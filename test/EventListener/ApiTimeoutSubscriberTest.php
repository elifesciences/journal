<?php

namespace test\eLife\Journal\EventListener;

use eLife\ApiClient\Exception\ApiTimeout;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\Exception\NetworkProblem;
use eLife\Journal\EventListener\ApiTimeoutSubscriber;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ApiTimeoutSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_turns_api_timeouts_into_a_504_exception()
    {
        $subscriber = new ApiTimeoutSubscriber();

        $exception = new ApiTimeout('Timeout', new GuzzleRequest('GET', 'http://www.example.com/'));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertEquals(new HttpException(504, 'Timeout', $exception), $event->getException());
    }

    /**
     * @test
     */
    public function it_turns_gateway_timeout_responses_into_a_504_exception()
    {
        $subscriber = new ApiTimeoutSubscriber();

        $exception = new BadResponse('Timeout', new GuzzleRequest('GET', 'http://www.example.com/'), new GuzzleResponse(504));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertEquals(new HttpException(504, 'Timeout', $exception), $event->getException());
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions()
    {
        $subscriber = new ApiTimeoutSubscriber();

        $exception = new NetworkProblem('Timeout', new GuzzleRequest('GET', 'http://www.example.com/'));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }

    /**
     * @test
     */
    public function it_ignores_other_responses()
    {
        $subscriber = new ApiTimeoutSubscriber();

        $exception = new BadResponse('Timeout', new GuzzleRequest('GET', 'http://www.example.com/'), new GuzzleResponse(503));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }
}
