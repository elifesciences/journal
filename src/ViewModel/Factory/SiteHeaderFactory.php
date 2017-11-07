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
use eLife\Patterns\ViewModel\SubjectFilter;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function eLife\Patterns\mixed_visibility_text;

final class SiteHeaderFactory
{
    private $urlGenerator;
    private $packages;
    private $requestStack;
    private $authorizationChecker;

    public function __construct(UrlGeneratorInterface $urlGenerator, Packages $packages, RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->urlGenerator = $urlGenerator;
        $this->packages = $packages;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createSiteHeader(Model $model = null, Profile $profile = null) : SiteHeader
    {
        if ($this->requestStack->getCurrentRequest() && 'search' !== $this->requestStack->getCurrentRequest()->get('_route')) {
            $searchItem = NavLinkedItem::asIcon(new Link('Search', $this->urlGenerator->generate('search')),
                new Picture(
                    [
                        ['srcset' => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-ic.svg')],
                    ],
                    new Image(
                        $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-ic_1x.png'),
                        [
                            48 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-ic_2x.png'),
                            24 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-ic_1x.png'),
                        ],
                        'Search icon'
                    )
                ),
                false,
                true,
                'search'
            );
        } else {
            $searchItem = NavLinkedItem::asIcon(new Link('Search'),
                new Picture(
                    [
                        ['srcset' => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-disabled-ic.svg')],
                    ],
                    new Image(
                        $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-disabled-ic_1x.png'),
                        [
                            48 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-disabled-ic_2x.png'),
                            24 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-search-disabled-ic_1x.png'),
                        ],
                        'Search icon'
                    )
                ),
                false,
                true,
                'search'
            );
        }

        $primaryLinks = SiteHeaderNavBar::primary([
            NavLinkedItem::asIcon(
                new Link('Menu', '#mainMenu'),
                new Picture(
                    [
                        ['srcset' => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-menu-ic.svg')],
                    ],
                    new Image(
                        $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-menu-ic_1x.png'),
                        [
                            48 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-menu-ic_2x.png'),
                            24 => $this->packages->getUrl('assets/patterns/img/patterns/molecules/nav-primary-menu-ic_1x.png'),
                        ],
                        'Menu icon'
                    )
                ),
                true,
                false,
                'menu'
            ),
            NavLinkedItem::asLink(new Link('Home', $this->urlGenerator->generate('home'))),
            NavLinkedItem::asLink(new Link('Magazine', $this->urlGenerator->generate('magazine'))),
            $searchItem,
        ]);

        $secondaryLinks = [
            NavLinkedItem::asLink(new Link('About', $this->urlGenerator->generate('about'))),
            NavLinkedItem::asLink(new Link('Labs', $this->urlGenerator->generate('labs'))),
            NavLinkedItem::asLink(new Link('Community', $this->urlGenerator->generate('community'))),
            NavLinkedItem::asButton(
                Button::link('Submit my research', 'http://submit.elifesciences.org/', Button::SIZE_EXTRA_SMALL)
            ),
        ];

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && $profile) {
            $secondaryLinks[] = NavLinkedItem::asLoginControl(
                LoginControl::loggedIn(
                    $this->urlGenerator->generate('profile', ['id' => $profile->getId()]),
                    $profile->getDetails()->getPreferredName(),
                    new Picture(
                        [
                            [
                                'srcset' => $this->packages->getUrl('assets/patterns/img/icons/profile.svg'),
                                'type' => 'image/svg+xml',
                            ],
                        ],
                        new Image(
                            $this->packages->getUrl('assets/patterns/img/icons/profile.png'),
                            [
                                70 => $this->packages->getUrl('assets/patterns/img/icons/profile@2x.png'),
                                35 => $this->packages->getUrl('assets/patterns/img/icons/profile.png'),
                            ],
                            'Profile icon'
                        )
                    ),
                    'View my profile',
                    [
                        'Manage my ORCID' => 'https://orcid.org/my-orcid',
                        'Log out' => $this->urlGenerator->generate('log-out'),
                    ]
                )
            );
        } elseif ($this->authorizationChecker->isGranted('FEATURE_CAN_AUTHENTICATE')) {
            $secondaryLinks[] = NavLinkedItem::asLoginControl(
                LoginControl::notLoggedIn(
                    mixed_visibility_text('', 'Log in/Register', '(via ORCID)'),
                    $this->urlGenerator->generate('log-in')
                )
            );
        }

        $secondaryLinks = SiteHeaderNavBar::secondary($secondaryLinks);

        if ($model instanceof HasSubjects) {
            $subject = $model->getSubjects()[0];
        } elseif ($model instanceof Subject) {
            $subject = $model;
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

        return new SiteHeader($this->urlGenerator->generate('home'), $primaryLinks, $secondaryLinks, $searchBox);
    }
}
