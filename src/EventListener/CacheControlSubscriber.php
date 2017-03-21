<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

final class CacheControlSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->isMethodSafe() || $response instanceof StreamedResponse || Response::HTTP_NOT_MODIFIED === $response->getStatusCode()) {
            return;
        }

        if ('no-cache' === $response->headers->get('Cache-Control')) {
            // Default Symfony value, so treat as untouched.

            $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=300, stale-if-error=86400');
        }

        $response->setEtag(md5($response->getContent()));
        if (!$response->headers->hasCacheControlDirective('no-store')) {
            $response->headers->set('Vary', 'Cookie', false);
        }

        $response->isNotModified($request);
    }
}
