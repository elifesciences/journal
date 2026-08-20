<?php

namespace eLife\Journal\EventListener;

use eLife\Journal\Exception\EarlyResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class EarlyResponseSubscriber implements EventSubscriberInterface
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

        if ($exception instanceof EarlyResponse) {
            $event->setResponse($exception->getResponse());
        }
    }
}
