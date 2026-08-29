<?php

namespace eLife\Journal\EventListener;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use GuzzleHttp\Psr7\UriResolver;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function strpos;
use GuzzleHttp\Psr7\Utils;

final class PreloadLinkSubscriber implements EventSubscriberInterface
{
    private $packages;
    private $preloads;

    public function __construct(Packages $packages, array $preloads)
    {
        $this->packages = $packages;
        $this->preloads = $preloads;
    }

    public static function getSubscribedEvents() : array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', 1]];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->preloads || !$event->isMainRequest() || 0 !== strpos($response->headers->get('Content-Type', 'text/html'), 'text/html')) {
            return;
        }

        $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());

        foreach ($this->preloads as $preload) {
            $uri = (string) UriResolver::resolve(Utils::uriFor('assets/patterns/'), Utils::uriFor($preload['uri']));

            $link = (new Link('preload', $this->packages->getUrl($uri)))
                ->withAttribute('as', $preload['as'])
                ->withAttribute('type', $preload['type'])
                ->withAttribute('nopush', true)
            ;

            $linkProvider = $linkProvider->withLink($link);
        }

        $request->attributes->set('_links', $linkProvider);
    }
}
