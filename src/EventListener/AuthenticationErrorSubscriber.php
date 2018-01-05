<?php

namespace eLife\Journal\EventListener;

use eLife\Patterns\ViewModel\InfoBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthenticationErrorSubscriber implements EventSubscriberInterface
{
    private $authenticationUtils;

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
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
            $message = 'Please adjust your <a href="https://orcid.org/my-orcid">ORCID privacy settings</a> for eLife to display your name.';
        }

        $event->getRequest()
            ->getSession()
            ->getFlashBag()
            ->add(InfoBar::TYPE_ATTENTION, $message ?? 'Failed to log in, please try again.');
    }
}
