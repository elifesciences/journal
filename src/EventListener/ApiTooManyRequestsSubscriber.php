<?php

namespace eLife\Journal\EventListener;

use eLife\ApiClient\Exception\BadResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class ApiTooManyRequestsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof BadResponse && Response::HTTP_TOO_MANY_REQUESTS === $exception->getResponse()->getStatusCode()) {
            $event->setThrowable(new TooManyRequestsHttpException(60, $exception->getMessage(), $exception));
        }
    }
}
