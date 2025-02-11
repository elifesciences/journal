<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\CompactForm;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Input;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\NavLinkedItem;
use eLife\Patterns\ViewModel\SearchBox;
use eLife\Patterns\ViewModel\SiteHeader;
use eLife\Patterns\ViewModel\SiteHeaderNavBar;
use eLife\Patterns\ViewModel\SiteHeaderTitle;
use eLife\Patterns\ViewModel\SubjectFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SiteHeaderFactory
{
    private $urlGenerator;
    private $requestStack;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function createSiteHeader(Model $item = null) : SiteHeader
    {
        $primaryLinks = SiteHeaderNavBar::primary([
            NavLinkedItem::asLink(
                new Link('Menu', '#mainMenu'),
                false
            ),
            NavLinkedItem::asLink(new Link('Home', $this->urlGenerator->generate('home'))),
            NavLinkedItem::asLink(new Link('Browse', $this->urlGenerator->generate('browse'))),
            NavLinkedItem::asLink(new Link('Magazine', $this->urlGenerator->generate('magazine'))),
            NavLinkedItem::asLink(new Link('Community', $this->urlGenerator->generate('community'))),
            NavLinkedItem::asLink(new Link('About', $this->urlGenerator->generate('about')))
        ]);

        $secondaryLinks = [
            NavLinkedItem::asLink(new Link('Search', $this->urlGenerator->generate('search')), true),
            NavLinkedItem::asLink(new Link('Alerts', $this->urlGenerator->generate('content-alerts'))),
            NavLinkedItem::asButton(
                Button::link('Submit your research', $this->urlGenerator->generate('submit-your-research'), Button::SIZE_EXTRA_SMALL, Button::STYLE_DEFAULT, true, false, 'submitResearchButton')
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
