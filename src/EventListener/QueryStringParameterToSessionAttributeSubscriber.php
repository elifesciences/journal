<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class QueryStringParameterToSessionAttributeSubscriber implements EventSubscriberInterface
{
    private $queryStringParameter;
    private $attribute;

    public function __construct(string $queryStringParameter, string $attribute)
    {
        $this->queryStringParameter = $queryStringParameter;
        $this->attribute = $attribute;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 127],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->query->has($this->queryStringParameter)) {
            return;
        }

        $request->getSession()->set($this->attribute, $request->query->get($this->queryStringParameter));
    }
}
