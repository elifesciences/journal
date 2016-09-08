<?php

namespace eLife\Journal\ViewModel;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\Footer;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MainMenu;
use eLife\Patterns\ViewModel\MainMenuLink;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FooterFactory
{
    private $subjectsClient;
    private $urlGenerator;

    public function __construct(
        SubjectsClient $subjectsClient,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->subjectsClient = $subjectsClient;
        $this->urlGenerator = $urlGenerator;
    }

    public function createFooter() : PromiseInterface
    {
        return $this->subjectsClient
            ->listSubjects(['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)], 1, 50, false)
            ->then(function (Result $result) {
                $subjects = [];
                foreach ($result['items'] as $subject) {
                    $subjects[] = new Link(
                        $subject['name'],
                        $this->urlGenerator->generate('subject', ['id' => $subject['id']])
                    );
                }

                return new Footer(
                    new MainMenu([
                        new MainMenuLink('Subjects', $subjects),
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
                                $this->urlGenerator->generate('archive-year', ['year' => (date('Y', time()) - 1)])),
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
                            $this->urlGenerator->generate('archive-year', ['year' => (date('Y', time()) - 1)])),
                        new Link('Labs', $this->urlGenerator->generate('labs')),
                        new Link('For the press', $this->urlGenerator->generate('press-packs')),
                        new Link('Resources', $this->urlGenerator->generate('resources')),
                    ]
                );
            });
    }
}
