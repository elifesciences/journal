<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RemoveSessionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -1000]];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session || (!$session->isStarted() && !$request->hasPreviousSession())) {
            return;
        }

        if (empty($session->all())) {
            $session->invalidate();
            $event->getResponse()->headers->clearCookie($session->getName());
        }
    }
}
