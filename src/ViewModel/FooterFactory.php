<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ViewModel\Footer;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MainMenu;
use eLife\Patterns\ViewModel\MainMenuLink;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Puli\UrlGenerator\Api\UrlGenerator as PuliUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FooterFactory
{
    private $urlGenerator;
    private $puliUrlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator, PuliUrlGenerator $puliUrlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->puliUrlGenerator = $puliUrlGenerator;
    }

    public function createFooter() : PromiseInterface
    {
        return new FulfilledPromise(
            new Footer(
                parse_url($this->puliUrlGenerator->generateUrl('/elife/patterns/assets'), PHP_URL_PATH),
                new MainMenu([
                    new MainMenuLink('eLife', [
                        new Link('How to publish',
                            'http://submit.elifesciences.org/html/elife_author_instructions.html'),
                        new Link('About', $this->urlGenerator->generate('about')),
                        new Link('Who we work with', $this->urlGenerator->generate('who-we-work-with')),
                        new Link('Alerts', $this->urlGenerator->generate('alerts')),
                        new Link('Contact', $this->urlGenerator->generate('contact')),
                        new Link('Terms and policy', $this->urlGenerator->generate('terms')),
                    ]),
                    new MainMenuLink('Resources', [
                        new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                        new Link('Monthly archive',
                            $this->urlGenerator->generate('archive-year', ['year' => (date('Y') - 1)])),
                        new Link('Labs', $this->urlGenerator->generate('labs')),
                        new Link('For the press', $this->urlGenerator->generate('press-packs')),
                        new Link('Resources', $this->urlGenerator->generate('resources')),
                    ]),
                ]),
                [
                    new Link('About', $this->urlGenerator->generate('about')),
                    new Link('Who we work with', $this->urlGenerator->generate('who-we-work-with')),
                    new Link('Alerts', $this->urlGenerator->generate('alerts')),
                    new Link('Contact', $this->urlGenerator->generate('contact')),
                    new Link('Terms and policy', $this->urlGenerator->generate('terms')),
                    new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                    new Link('Monthly archive',
                        $this->urlGenerator->generate('archive-year', ['year' => (date('Y') - 1)])),
                    new Link('Labs', $this->urlGenerator->generate('labs')),
                    new Link('For the press', $this->urlGenerator->generate('press-packs')),
                    new Link('Resources', $this->urlGenerator->generate('resources')),
                ]
            )
        );
    }
}
