<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class EndEmptySessionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -1000]];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session instanceof Session || (!$session->isStarted() && !$request->hasPreviousSession())) {
            return;
        }

        if ($session->isEmpty()) {
            $session->invalidate();
            $event->getResponse()->headers->clearCookie($session->getName());
        }
    }
}
