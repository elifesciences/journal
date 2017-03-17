<?php

namespace eLife\Journal\EventListener;

use eLife\ApiClient\Exception\ApiTimeout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApiTimeoutSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof ApiTimeout) {
            $event->setException(new HttpException(Response::HTTP_GATEWAY_TIMEOUT, $exception->getMessage(), $exception));
        }
    }
}
