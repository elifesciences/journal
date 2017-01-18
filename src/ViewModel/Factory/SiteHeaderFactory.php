<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\CompactForm;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\Input;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\NavLinkedItem;
use eLife\Patterns\ViewModel\Picture;
use eLife\Patterns\ViewModel\SearchBox;
use eLife\Patterns\ViewModel\SiteHeader;
use eLife\Patterns\ViewModel\SiteHeaderNavBar;
use Puli\UrlGenerator\Api\UrlGenerator as PuliUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SiteHeaderFactory
{
    private $urlGenerator;
    private $puliUrlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, PuliUrlGenerator $puliUrlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->puliUrlGenerator = $puliUrlGenerator;
    }

    public function createSiteHeader() : SiteHeader
    {
        $primaryLinks = SiteHeaderNavBar::primary([
            NavLinkedItem::asIcon(
                new Link('Menu', '#mainMenu'),
                new Picture(
                    [
                        ['srcset' => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-menu-ic.svg')],
                    ],
                    new Image(
                        $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-menu-ic_1x.png'),
                        [
                            48 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-menu-ic_2x.png'),
                            24 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-menu-ic_1x.png'),
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
            NavLinkedItem::asIcon(new Link('Search', $this->urlGenerator->generate('search')),
                new Picture(
                    [
                        ['srcset' => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-search-ic.svg')],
                    ],
                    new Image(
                        $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-search-ic_1x.png'),
                        [
                            48 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-search-ic_2x.png'),
                            24 => $this->puliUrlGenerator->generateUrl('/elife/patterns/assets/img/patterns/molecules/nav-primary-search-ic_1x.png'),
                        ],
                        'Search icon'
                    )
                ),
                false,
                true,
                'search'
            ),
        ]);

        $secondaryLinks = SiteHeaderNavBar::secondary([
            NavLinkedItem::asLink(new Link('About', $this->urlGenerator->generate('about'))),
            NavLinkedItem::asLink(new Link('Labs', $this->urlGenerator->generate('labs'))),
            NavLinkedItem::asLink(new Link('Community', $this->urlGenerator->generate('community'))),
            NavLinkedItem::asButton(
                Button::link('Submit my research', 'http://submit.elifesciences.org/', Button::SIZE_EXTRA_SMALL)
            ),
        ]);

        $searchBox = new SearchBox(
            new CompactForm(
                new Form($this->urlGenerator->generate('search'), 'search', 'GET'),
                new Input('Search by keyword or author', 'search', 'for', null, 'Search by keyword or author'),
                'Search'
            )
        );

        return new SiteHeader($this->urlGenerator->generate('home'), $primaryLinks, $secondaryLinks, $searchBox);
    }
}
