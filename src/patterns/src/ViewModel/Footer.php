<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Footer implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $year;
    private $mainMenu;
    private $title;
    private $listHeading;
    private $links;
    private $button;
    private $footerMenuLinks;
    private $logos;

    public function __construct(
        MainMenu $mainMenu,
        array $footerMenuLinks,
        InvestorLogos $investorLogos
    ) {
        Assertion::notEmpty($footerMenuLinks);
        Assertion::allIsInstanceOf($footerMenuLinks, Link::class);

        $this->year = (int) date('Y');
        $this->mainMenu = true;
        $this->title = $mainMenu['title'];
        $this->listHeading = $mainMenu['listHeading'];
        $this->links = $mainMenu['links'];
        $this->button = $mainMenu['button'];
        $this->footerMenuLinks = $footerMenuLinks;
        $this->logos = $investorLogos['logos'];
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/footer.mustache';
    }
}
