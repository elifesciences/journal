<?php

namespace eLife\Journal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FragmentLinkRewriteSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents() : array
    {
        return ['kernel.response' => 'onKernelResponse'];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        $response = $event->getResponse();

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return;
        }

        switch ($attributes->get('_route')) {
            case 'article':
                $this->rewrite($response, $this->urlGenerator->generate('article-figures', ['volume' => $attributes->get('volume'), 'id' => $attributes->get('id')]));
                break;
            case 'article-figures':
                $this->rewrite($response, $this->urlGenerator->generate('article', ['volume' => $attributes->get('volume'), 'id' => $attributes->get('id')]));
                break;
        }
    }

    private function rewrite(Response $response, string $alternateFragmentPage)
    {
        preg_match_all('/id=["\']([^\s]+)["\']/', $response->getContent(), $matches);

        $response->setContent(preg_replace_callback('/href=["\']#([^\s]+)["\']/', function (array $match) use ($alternateFragmentPage, $matches) {
            if (in_array($match[1], $matches[1])) {
                return $match[0];
            }

            return sprintf('href="%s#%s"', $alternateFragmentPage, $match[1]);
        }, $response->getContent()));
    }
}
