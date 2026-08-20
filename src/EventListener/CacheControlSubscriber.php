<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

final class CacheControlSubscriber implements EventSubscriberInterface
{
    const NOT_FOUND_MAX_AGE = 5 * 60;

    private $cacheControl;

    public function __construct(string $cacheControl)
    {
        $this->cacheControl = $cacheControl;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$request->isMethodCacheable() || $response instanceof StreamedResponse || Response::HTTP_NOT_MODIFIED === $response->getStatusCode() || ($request->hasSession() && $request->getSession()->isStarted() || $request->hasPreviousSession())) {
            return;
        }

        // The session wasn't used, so this response is safe to cache; stop Symfony's
        // SessionListener from forcing it private just because the security firewall's
        // AccessListener touched the (anonymous) token storage while checking access control.
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, true);

        if ('no-cache, private' === $response->headers->get('Cache-Control')) {
            // Default Symfony value, so treat as untouched.

            $response->headers->set('Cache-Control', $this->cacheControl);
        }

        // Cap 404 cache length (see https://github.com/elifesciences/issues/issues/4400)
        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            if (($response->getMaxAge() ?? 0) > self::NOT_FOUND_MAX_AGE) {
                $response->setMaxAge(self::NOT_FOUND_MAX_AGE);
                $response->headers->removeCacheControlDirective('s-maxage');
            }
            $response->headers->removeCacheControlDirective('stale-while-revalidate');
        }

        $response->setEtag(md5($response->getContent()));
        if (!$response->headers->hasCacheControlDirective('no-store')) {
            $response->headers->set('Vary', 'Cookie', false);
        }

        $response->isNotModified($request);
    }
}
