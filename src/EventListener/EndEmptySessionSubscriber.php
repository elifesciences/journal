<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class EndEmptySessionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -127]]; # Before TestSessionListener
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session instanceof Session || !$session->isStarted() || !$session->isEmpty()) {
            return;
        }

        $session->invalidate();
        $event->getResponse()->headers->clearCookie($session->getName());
    }
}
