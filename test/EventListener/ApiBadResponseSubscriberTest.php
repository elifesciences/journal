<?php

namespace test\eLife\Journal\EventListener;

use eLife\ApiClient\Exception\ApiException;
use eLife\ApiClient\Exception\BadResponse;
use eLife\Journal\EventListener\ApiBadResponseSubscriber;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use test\eLife\Journal\AppKernelTestCase;

final class ApiBadResponseSubscriberTest extends KernelTestCase
{
    use AppKernelTestCase;

    /**
     * @test
     * @dataProvider statusCodeProvider
     */
    public function it_turns_exceptions_into_http_exceptions(int $statusCode)
    {
        $subscriber = new ApiBadResponseSubscriber();

        $exception = new BadResponse('foo', new Request('GET', 'http://www.example.com/'), new Response($statusCode));
        $event = $this->createGetResponseForExceptionEvent($exception);

        $subscriber->onKernelException($event);

        $this->assertInstanceOf(HttpExceptionInterface::class, $event->getException());
        $this->assertSame($statusCode, $event->getException()->getStatusCode());
        $this->assertSame($exception, $event->getException()->getPrevious());
    }

    public function statusCodeProvider() : array
    {
        return [
            [HttpFoundationResponse::HTTP_GONE],
            [HttpFoundationResponse::HTTP_NOT_FOUND],
            [HttpFoundationResponse::HTTP_SERVICE_UNAVAILABLE],
        ];
    }

    /**
     * @test
     * @dataProvider nonBadResponseExceptionProvider
     */
    public function it_ignores_non_bad_response_exceptions(Exception $exception)
    {
        $subscriber = new ApiBadResponseSubscriber();

        $event = $event = $this->createGetResponseForExceptionEvent($exception);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }

    public function nonBadResponseExceptionProvider() : array
    {
        return [
            [new Exception('foo')],
            [new ApiException('foo')],
        ];
    }

    /**
     * @test
     */
    public function it_ignores_exceptions_on_sub_requests()
    {
        $subscriber = new ApiBadResponseSubscriber();

        $exception = new BadResponse('foo', new Request('GET', 'http://www.example.com/'), new Response());
        $event = $this->createGetResponseForExceptionEvent($exception, HttpKernelInterface::SUB_REQUEST);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }

    private function createGetResponseForExceptionEvent(Exception $exception, int $requestType = HttpKernelInterface::MASTER_REQUEST) : GetResponseForExceptionEvent
    {
        return new GetResponseForExceptionEvent($this->createKernel(), new HttpFoundationRequest(), $requestType, $exception);
    }
}
