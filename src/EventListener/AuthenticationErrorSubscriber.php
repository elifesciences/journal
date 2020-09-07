<?php

namespace eLife\Journal\EventListener;

use eLife\Patterns\ViewModel\InfoBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthenticationErrorSubscriber implements EventSubscriberInterface
{
    private $authenticationUtils;
    private $urlGenerator;

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function __construct(AuthenticationUtils $authenticationUtils, UrlGeneratorInterface $urlGenerator)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || !$event->getRequest()->getSession()->isStarted()) {
            return;
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        if (!$error) {
            return;
        }

        if ('No name visible' === $error->getMessage()) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('log-in-orcid-visibility-setting')));
        }

        $event->getRequest()
            ->getSession()
            ->getFlashBag()
            ->add(InfoBar::TYPE_ATTENTION, $message ?? 'Failed to log in, please try again.');
    }
}
