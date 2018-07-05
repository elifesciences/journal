<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

final class CacheControlSubscriber implements EventSubscriberInterface
{
    private $maxAge;
    private $staleWhileRevalidate;
    private $staleIfError;

    public function __construct(int $maxAge, int $staleWhileRevalidate, int $staleIfError)
    {
        $this->maxAge = $maxAge;
        $this->staleWhileRevalidate = $staleWhileRevalidate;
        $this->staleIfError = $staleIfError;
    }

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

        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$request->isMethodCacheable() || $response instanceof StreamedResponse || Response::HTTP_NOT_MODIFIED === $response->getStatusCode() || ($request->hasSession() && $request->getSession()->isStarted() || $request->hasPreviousSession())) {
            return;
        }

        if ('no-cache, private' === $response->headers->get('Cache-Control')) {
            // Default Symfony value, so treat as untouched.

            $response->headers->set('Cache-Control', "public, max-age={$this->maxAge}, stale-while-revalidate={$this->staleWhileRevalidate}, stale-if-error={$this->staleIfError}");
        }

        $response->setEtag(md5($response->getContent()));
        if (!$response->headers->hasCacheControlDirective('no-store')) {
            $response->headers->set('Vary', 'Cookie', false);
        }

        $response->isNotModified($request);
    }
}
