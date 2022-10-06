<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Profile;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\CompactForm;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\Input;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\LoginControl;
use eLife\Patterns\ViewModel\NavLinkedItem;
use eLife\Patterns\ViewModel\Picture;
use eLife\Patterns\ViewModel\SearchBox;
use eLife\Patterns\ViewModel\SiteHeader;
use eLife\Patterns\ViewModel\SiteHeaderNavBar;
use eLife\Patterns\ViewModel\SiteHeaderTitle;
use eLife\Patterns\ViewModel\SubjectFilter;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use function eLife\Patterns\mixed_visibility_text;

final class SiteHeaderFactory
{
    private $urlGenerator;
    private $packages;
    private $requestStack;
    private $authorizationChecker;
    private $submitUrl;

    public function __construct(UrlGeneratorInterface $urlGenerator, Packages $packages, RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker, string $submitUrl)
    {
        $this->urlGenerator = $urlGenerator;
        $this->packages = $packages;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->submitUrl = $submitUrl;
    }

    public function createSiteHeader(Model $item = null, Profile $profile = null) : SiteHeader
    {
        if ($this->requestStack->getCurrentRequest() && 'search' !== $this->requestStack->getCurrentRequest()->get('_route')) {
            $searchItem = NavLinkedItem::asLink(new Link('Search the eLife site', $this->urlGenerator->generate('search')),
                true
            );
        } else {
            $searchItem = NavLinkedItem::asLink(new Link('Search'),
                true
            );
        }

        $primaryLinks = SiteHeaderNavBar::primary([
            NavLinkedItem::asLink(
                new Link('Menu', '#mainMenu'),
                false
            ),
            NavLinkedItem::asLink(new Link('Home', $this->urlGenerator->generate('home'))),
            NavLinkedItem::asLink(new Link('Magazine', $this->urlGenerator->generate('magazine'))),
            NavLinkedItem::asLink(new Link('Community', $this->urlGenerator->generate('community'))),
            NavLinkedItem::asLink(new Link('About', $this->urlGenerator->generate('about'))),
//            $searchItem,
        ]);

        if ($this->authorizationChecker->isGranted('FEATURE_XPUB') && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            $submitUrl = $this->urlGenerator->generate('submit');
        }

        $secondaryLinks = [
            NavLinkedItem::asLink(new Link('Search', $this->urlGenerator->generate('search')), true),
            NavLinkedItem::asLink(new Link('Alerts', $this->urlGenerator->generate('alerts'))),
            NavLinkedItem::asButton(
                Button::link('Submit my research', $submitUrl ?? $this->submitUrl, Button::SIZE_EXTRA_SMALL, Button::STYLE_DEFAULT, true, false, 'submitResearchButton')
            ),
        ];

        $secondaryLinks = SiteHeaderNavBar::secondary($secondaryLinks);

        if ($item instanceof HasSubjects) {
            $subject = $item->getSubjects()[0];
        } elseif ($item instanceof Subject) {
            $subject = $item;
        } else {
            $subject = null;
        }

        if ($this->requestStack->getCurrentRequest() && 'search' !== $this->requestStack->getCurrentRequest()->get('_route')) {
            $searchBox = new SearchBox(
                new CompactForm(
                    new Form($this->urlGenerator->generate('search'), 'search', 'GET'),
                    new Input('Search by keyword or author', 'search', 'for', null, 'Search by keyword or author'),
                    'Search'
                ),
                $subject ? new SubjectFilter('subjects[]', $subject->getId(), $subject->getName()) : null
            );
        } else {
            $searchBox = null;
        }

        return new SiteHeader(new SiteHeaderTitle($this->urlGenerator->generate('home')), $primaryLinks, $secondaryLinks, $searchBox);
    }
}
