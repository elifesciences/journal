<?php

namespace eLife\Journal\EventListener;

use eLife\ApiClient\Exception\BadResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApiBadResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return ['kernel.exception' => 'onKernelException'];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getException();

        if (false === $exception instanceof BadResponse) {
            return;
        }

        /** @var BadResponse $exception */
        switch ($exception->getResponse()->getStatusCode()) {
            case Response::HTTP_GONE:
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_SERVICE_UNAVAILABLE:
                $exception = new HttpException($exception->getResponse()->getStatusCode(), $exception->getMessage(), $exception);
                break;
        }

        $event->setException($exception);
    }
}
