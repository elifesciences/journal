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
                'format' => 'image/png',
                'uri' => 'http://www.alpsp.org/',
            ],
            [
                'name' => 'Committee on Publication Ethics',
                'filename' => 'cope',
                'format' => 'image/png',
                'uri' => 'http://publicationethics.org/',
            ],
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'format' => 'image/svg+xml',
                'uri' => 'https://oaspa.org/',
            ],
            [
                'name' => 'ORCID',
                'filename' => 'orcid',
                'format' => 'image/svg+xml',
                'uri' => 'https://orcid.org/',
            ],
        ];

        $serviceProviders = [
            [
                'name' => 'Amazon Web Services',
                'filename' => 'aws',
                'format' => 'image/svg+xml',
                'uri' => 'https://aws.amazon.com/',
            ],
            [
                'name' => 'Digirati',
                'filename' => 'digirati',
                'format' => 'image/svg+xml',
                'uri' => 'https://digirati.com/',
            ],
            [
                'name' => 'Editorial Office Ltd',
                'filename' => 'ed-office',
                'format' => 'image/svg+xml',
                'uri' => 'http://editorialoffice.co.uk/',
            ],
            [
                'name' => 'eJournalPress',
                'filename' => 'ejp',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.ejournalpress.com/',
            ],
            [
                'name' => 'Exeter Premedia Services',
                'filename' => 'exeter',
                'format' => 'image/png',
                'uri' => 'http://www.exeterpremedia.com/',
            ],
            [
                'name' => 'GitHub',
                'filename' => 'github',
                'format' => 'image/svg+xml',
                'uri' => 'https://github.com/',
            ],
            [
                'name' => 'Glencoe Software',
                'filename' => 'glencoe',
                'format' => 'image/svg+xml',
                'uri' => 'https://glencoesoftware.com/',
            ],
            [
                'name' => 'JIRA',
                'filename' => 'jira',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.atlassian.com/software/jira',
            ],
            [
                'name' => 'Loggly',
                'filename' => 'loggly',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.loggly.com/',
            ],
            [
                'name' => 'The Naked Scientists',
                'filename' => 'naked-scientists',
                'format' => 'image/png',
                'uri' => 'https://www.thenakedscientists.com/',
            ],
            [
                'name' => 'New Relic',
                'filename' => 'new-relic',
                'format' => 'image/svg+xml',
                'uri' => 'https://newrelic.com/',
            ],
            [
                'name' => 'Publons',
                'filename' => 'publons',
                'format' => 'image/svg+xml',
                'uri' => 'https://publons.com/',
            ],
            [
                'name' => 'Slack',
                'filename' => 'slack',
                'format' => 'image/svg+xml',
                'uri' => 'https://slack.com/',
            ],
        ];

        $content = [
            [
                'name' => 'CLOCKSS',
                'filename' => 'clockss',
                'format' => 'image/png',
                'uri' => 'https://www.clockss.org/',
            ],
            [
                'name' => 'Europe PubMed Central',
                'filename' => 'europe-pmc',
                'format' => 'image/png',
                'uri' => 'https://europepmc.org/',
            ],
            [
                'name' => 'Go OA',
                'filename' => 'gooa',
                'format' => 'image/png',
                'uri' => 'http://gooa.las.ac.cn/',
            ],
            [
                'name' => 'Jisc',
                'filename' => 'jisc',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.jisc.ac.uk/',
            ],
            [
                'name' => 'LOCKSS',
                'filename' => 'lockss',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.lockss.org/',
            ],
            [
                'name' => 'Mendeley',
                'filename' => 'mendeley',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.mendeley.com/',
            ],
            [
                'name' => 'Paperity',
                'filename' => 'paperity',
                'format' => 'image/svg+xml',
                'uri' => 'http://paperity.org/',
            ],
            [
                'name' => 'PubMed Central',
                'filename' => 'pmc',
                'format' => 'image/png',
                'uri' => 'https://www.ncbi.nlm.nih.gov/pmc/',
            ],
            [
                'name' => 'SHARE',
                'filename' => 'share',
                'format' => 'image/svg+xml',
                'uri' => 'http://www.share-research.org/',
            ],
        ];

        $committees = [
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'format' => 'image/svg+xml',
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'FORCE11',
                'filename' => 'force11',
                'format' => 'image/png',
                'uri' => 'https://www.force11.org/about/directors-and-advisors',
            ],
            [
                'name' => 'JATS for Reuse',
                'filename' => 'jats4r',
                'format' => 'image/png',
                'uri' => 'http://jats4r.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'format' => 'image/svg+xml',
                'uri' => 'https://oaspa.org/',
            ],
            [
                'name' => 'Publons',
                'filename' => 'publons',
                'format' => 'image/svg+xml',
                'uri' => 'https://publons.com/',
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
        return array_map(function (array $item) : ImageLink {
            $builder = $this->get('elife.journal.view_model.factory.picture_builder')
                ->create(function (string $format, int $width) use ($item) {
                    $extension = MediaTypes::toExtension($format);

                    $path = "assets/images/logos/{$item['filename']}";

                    if ('svg' !== $extension) {
                        $path .= "-{$width}";
                    }

                    return $this->get('assets.packages')->getUrl("{$path}.{$extension}");
                }, $item['format'], 180, null, $item['name']);

            return new ImageLink(
                $item['uri'],
                $builder->build()
            );
        }, $items);
    }
}
