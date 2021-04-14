<?php

namespace eLife\Journal\ViewModel\Factory;

use eLife\Journal\Helper\MediaTypes;
use eLife\Patterns\ViewModel\Footer;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\InvestorLogos;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\MainMenu;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class FooterFactory
{
    private $urlGenerator;
    private $pictureBuilderFactory;
    private $packages;
    private $authorizationChecker;

    public function __construct(UrlGeneratorInterface $urlGenerator, PictureBuilderFactory $pictureBuilderFactory, Packages $packages, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->urlGenerator = $urlGenerator;
        $this->pictureBuilderFactory = $pictureBuilderFactory;
        $this->packages = $packages;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFooter() : Footer
    {
        $now = time();
        $year = gmdate('Y', $now);
        if (1 == gmdate('n', $now)) {
            --$year;
        }

        $investors = [
            [
                'name' => 'Howard Hughes Medical Institute',
                'filename' => 'hhmi',
                'type' => 'image/svg+xml',
            ],
            [
                'name' => 'Wellcome Trust',
                'filename' => 'wellcome',
                'type' => 'image/svg+xml',
            ],
            [
                'name' => 'Max-Planck-Gesellschaft',
                'filename' => 'max',
                'type' => 'image/svg+xml',
            ],
            [
                'name' => 'Knut and Alice Wallenberg Foundation',
                'filename' => 'kaw',
                'type' => 'image/svg+xml',
            ],
        ];

        return new Footer(
            new MainMenu([
                new Link('Research categories', $this->urlGenerator->generate('subjects')),
                new Link('Author guide',
                    'https://reviewer.elifesciences.org/author-guide/editorial-process'),
                new Link('Reviewer guide',
                    'https://reviewer.elifesciences.org/reviewer-guide/review-process'),
                new Link('About', $this->urlGenerator->generate('about')),
                new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                new Link('Community', $this->urlGenerator->generate('community')),
                new Link('Innovation', $this->urlGenerator->generate('labs')),
            ]),
            [
                new Link('About', $this->urlGenerator->generate('about')),
                new Link('Jobs', $this->urlGenerator->generate('job-adverts')),
                new Link('Who we work with', $this->urlGenerator->generate('who-we-work-with')),
                new Link('Alerts', $this->urlGenerator->generate('alerts')),
                new Link('Contact', $this->urlGenerator->generate('contact')),
                new Link('Terms and conditions', $this->urlGenerator->generate('terms')),
                new Link('Privacy notice', $this->urlGenerator->generate('privacy')),
                new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                new Link('Monthly archive',
                    $this->urlGenerator->generate('archive-year', ['year' => $year])),
                new Link('Innovation', $this->urlGenerator->generate('labs')),
                new Link('For the press', $this->urlGenerator->generate('press-packs')),
                new Link('Resources', $this->urlGenerator->generate('resources')),
                new Link('XML, Data and API',
                    'http://developers.elifesciences.org'),
            ],
            new InvestorLogos(...array_map(function (array $item) {
                return $this->pictureBuilderFactory
                    ->create(function (string $type, int $width, int $height = null, float $scale) use ($item) {
                        $extension = MediaTypes::toExtension($type);

                        $path = "assets/images/investors/{$item['filename']}";

                        if ('svg' !== $extension) {
                            $path .= "@{$scale}x";
                        }

                        return $this->packages->getUrl("{$path}.{$extension}");
                    }, $item['type'], 185, null, $item['name'])
                    ->build();
            }, $investors))
        );
    }
}
