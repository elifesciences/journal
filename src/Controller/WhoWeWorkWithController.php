<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\MediaTypes;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\ImageLink;
use eLife\Patterns\ViewModel\ListHeading;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhoWeWorkWithController extends Controller
{
    public function whoWeWorkWithAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Who we work with';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $memberships = [
            [
                'name' => 'The Association of Learned & Professional Society Publishers',
                'filename' => 'alpsp',
                'type' => 'image/png',
                'uri' => 'http://www.alpsp.org/',
            ],
            [
                'name' => 'Committee on Publication Ethics',
                'filename' => 'cope',
                'type' => 'image/png',
                'uri' => 'http://publicationethics.org/',
            ],
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'Niso',
                'filename' => 'niso-logo',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.niso.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'type' => 'image/svg+xml',
                'uri' => 'https://oaspa.org/',
            ],
            [
                'name' => 'ORCID',
                'filename' => 'orcid',
                'type' => 'image/svg+xml',
                'uri' => 'https://orcid.org/',
            ],
        ];

        $serviceProviders = [
            [
                'name' => 'Amazon Web Services',
                'filename' => 'aws',
                'type' => 'image/svg+xml',
                'uri' => 'https://aws.amazon.com/',
            ],
            [
                'name' => 'Browserstack',
                'filename' => 'browserstack',
                'type' => 'image/png',
                'uri' => 'https://browserstack.com/',
            ],
            [
                'name' => 'Digirati',
                'filename' => 'digirati',
                'type' => 'image/svg+xml',
                'uri' => 'https://digirati.com/',
            ],
            [
                'name' => 'Editorial Office Ltd',
                'filename' => 'ed-office',
                'type' => 'image/svg+xml',
                'uri' => 'http://editorialoffice.co.uk/',
            ],
            [
                'name' => 'eJournalPress',
                'filename' => 'ejp',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.ejournalpress.com/',
            ],
            [
                'name' => 'Exeter Premedia Services',
                'filename' => 'exeter',
                'type' => 'image/png',
                'uri' => 'http://www.exeterpremedia.com/',
            ],
            [
                'name' => 'Fastly',
                'filename' => 'fastly',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.fastly.com/',
            ],
            [
                'name' => 'GitHub',
                'filename' => 'github',
                'type' => 'image/svg+xml',
                'uri' => 'https://github.com/',
            ],
            [
                'name' => 'Glencoe Software',
                'filename' => 'glencoe',
                'type' => 'image/svg+xml',
                'uri' => 'https://glencoesoftware.com/',
            ],
            [
                'name' => 'Hypothesis',
                'filename' => 'hypothesis',
                'type' => 'image/svg+xml',
                'uri' => 'https://hypothes.is/',
            ],
            [
                'name' => 'Loggly',
                'filename' => 'loggly',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.loggly.com/',
            ],
            [
                'name' => 'The Naked Scientists',
                'filename' => 'naked-scientists',
                'type' => 'image/png',
                'uri' => 'https://www.thenakedscientists.com/',
            ],
            [
                'name' => 'New Relic',
                'filename' => 'new-relic-logo',
                'type' => 'image/svg+xml',
                'uri' => 'https://newrelic.com/',
            ],
            [
                'name' => 'Publons',
                'filename' => 'publons',
                'type' => 'image/svg+xml',
                'uri' => 'https://publons.com/',
            ],
            [
                'name' => 'Slack',
                'filename' => 'slack',
                'type' => 'image/svg+xml',
                'uri' => 'https://slack.com/',
            ],
        ];

        $content = [
            [
                'name' => 'CLOCKSS',
                'filename' => 'clockss',
                'type' => 'image/png',
                'uri' => 'https://www.clockss.org/',
            ],
            [
                'name' => 'Europe PubMed Central',
                'filename' => 'pubmed',
                'type' => 'image/svg+xml',
                'uri' => 'https://europepmc.org/',
            ],
            [
                'name' => 'Go OA',
                'filename' => 'gooa',
                'type' => 'image/png',
                'uri' => 'http://gooa.las.ac.cn/',
            ],
            [
                'name' => 'Jisc',
                'filename' => 'jisc',
                'type' => 'image/png',
                'uri' => 'https://pubrouter.jisc.ac.uk/',
            ],
            [
                'name' => 'LOCKSS',
                'filename' => 'lockss',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.lockss.org/',
            ],
            [
                'name' => 'Mendeley',
                'filename' => 'mendeley',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.mendeley.com/',
            ],
            [
                'name' => 'Paperity',
                'filename' => 'paperity',
                'type' => 'image/svg+xml',
                'uri' => 'http://paperity.org/',
            ],
            [
                'name' => 'PubMed Central',
                'filename' => 'pmc',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.ncbi.nlm.nih.gov/pmc/',
            ],
            [
                'name' => 'SHARE',
                'filename' => 'share',
                'type' => 'image/svg+xml',
                'uri' => 'http://www.share-research.org/',
            ],
        ];

        $committees = [
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'Declaration on Research Assessment',
                'filename' => 'dora',
                'type' => 'image/png',
                'uri' => 'https://sfdora.org/',
            ],
            [
                'name' => 'Directory of Open Access Journals',
                'filename' => 'doaj',
                'type' => 'image/svg+xml',
                'uri' => 'https://doaj.org/',
            ],
            [
                'name' => 'FORCE11',
                'filename' => 'force11',
                'type' => 'image/png',
                'uri' => 'https://www.force11.org/about/directors-and-advisors',
            ],
            [
                'name' => 'Initiative for Open Citations',
                'filename' => 'io4c',
                'type' => 'image/svg+xml',
                'uri' => 'https://i4oc.org/',
            ],
            [
                'name' => 'JATS for Reuse',
                'filename' => 'jats4r',
                'type' => 'image/png',
                'uri' => 'http://jats4r.org/',
            ],
            [
                'name' => 'Review Commons',
                'filename' => 'review-commons-logo',
                'type' => 'image/svg+xml',
                'uri' => 'https://www.reviewcommons.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'type' => 'image/svg+xml',
                'uri' => 'https://oaspa.org/',
            ],
        ];

        $arguments['listings'] = [
            GridListing::forImageLinks($this->toImageLinks($memberships), new ListHeading('Memberships')),
            GridListing::forImageLinks($this->toImageLinks($serviceProviders), new ListHeading('Service providers')),
            GridListing::forImageLinks($this->toImageLinks($content), new ListHeading('Content availability and archiving')),
            GridListing::forImageLinks($this->toImageLinks($committees), new ListHeading('Committees and initiatives')),
        ];

        return new Response($this->get('templating')->render('::who-we-work-with.html.twig', $arguments));
    }

    private function toImageLinks(array $items) : array
    {
        $packages = $this->get('elife.assets.packages');

        return array_map(function (array $item) use ($packages) : ImageLink {
            $builder = $this->get('elife.journal.view_model.factory.picture_builder')
                ->create(function (string $type, int $width, int $height = null, float $scale) use ($item, $packages) {
                    $extension = MediaTypes::toExtension($type);

                    $path = "assets/images/logos/{$item['filename']}";

                    if ('svg' !== $extension) {
                        $path .= "@{$scale}x";
                    }

                    return $packages->getUrl("{$path}.{$extension}");
                }, $item['type'], 180, null, $item['name']);

            return new ImageLink(
                $item['uri'],
                $builder->build()
            );
        }, $items);
    }
}
