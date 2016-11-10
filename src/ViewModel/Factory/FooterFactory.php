<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\Patterns\ViewModel\Footer;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MainMenu;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FooterFactory
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function createFooter() : Footer
    {
        return new Footer(
            new MainMenu([
                new Link('Research categories', $this->urlGenerator->generate('subjects')),
                new Link('Author guide',
                    'https://submit.elifesciences.org/html/elife_author_instructions.html'),
                new Link('Reviewer guide',
                    'https://submit.elifesciences.org/html/elife_reviewer_instructions.html'),
                new Link('About', $this->urlGenerator->generate('about')),
                new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                new Link('Community', $this->urlGenerator->generate('community')),
                new Link('Labs', $this->urlGenerator->generate('labs')),
            ]),
            [
                new Link('About', $this->urlGenerator->generate('about')),
                new Link('Who we work with', $this->urlGenerator->generate('who-we-work-with')),
                new Link('Alerts', $this->urlGenerator->generate('alerts')),
                new Link('Contact', $this->urlGenerator->generate('contact')),
                new Link('Terms and policy', $this->urlGenerator->generate('terms')),
                new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                new Link('Monthly archive',
                    $this->urlGenerator->generate('archive-year', ['year' => (date('Y', time()) - 1)])),
                new Link('Labs', $this->urlGenerator->generate('labs')),
                new Link('For the press', $this->urlGenerator->generate('press-packs')),
                new Link('Resources', $this->urlGenerator->generate('resources')),
            ]
        );
    }
}
