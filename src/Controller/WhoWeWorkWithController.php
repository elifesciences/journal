<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\ImageLink;
use eLife\Patterns\ViewModel\Picture;
use Symfony\Component\HttpFoundation\Response;

final class WhoWeWorkWithController extends Controller
{
    public function whoWeWorkWithAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'Who we work with';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        $memberships = [
            [
                'name' => 'The Association of Learned & Professional Society Publishers',
                'filename' => 'alpsp',
                'svg' => false,
                'uri' => 'http://www.alpsp.org/',
            ],
            [
                'name' => 'Committee on Publication Ethics',
                'filename' => 'cope',
                'svg' => false,
                'uri' => 'http://publicationethics.org/',
            ],
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'svg' => true,
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'svg' => true,
                'uri' => 'https://oaspa.org/',
            ],
            [
                'name' => 'ORCID',
                'filename' => 'orcid',
                'svg' => true,
                'uri' => 'https://orcid.org/',
            ],
        ];

        $serviceProviders = [
            [
                'name' => 'Amazon Web Services',
                'filename' => 'aws',
                'svg' => true,
                'uri' => 'https://aws.amazon.com/',
            ],
            [
                'name' => 'Digirati',
                'filename' => 'digirati',
                'svg' => true,
                'uri' => 'https://digirati.com/',
            ],
            [
                'name' => 'Editorial Office Ltd',
                'filename' => 'ed-office',
                'svg' => true,
                'uri' => 'http://editorialoffice.co.uk/',
            ],
            [
                'name' => 'eJournalPress',
                'filename' => 'ejp',
                'svg' => true,
                'uri' => 'https://www.ejournalpress.com/',
            ],
            [
                'name' => 'Exeter Premedia Services',
                'filename' => 'exeter',
                'svg' => false,
                'uri' => 'http://www.exeterpremedia.com/',
            ],
            [
                'name' => 'GitHub',
                'filename' => 'github',
                'svg' => true,
                'uri' => 'https://github.com/',
            ],
            [
                'name' => 'Glencoe Software',
                'filename' => 'glencoe',
                'svg' => true,
                'uri' => 'https://glencoesoftware.com/',
            ],
            [
                'name' => 'JIRA',
                'filename' => 'jira',
                'svg' => true,
                'uri' => 'https://www.atlassian.com/software/jira',
            ],
            [
                'name' => 'Loggly',
                'filename' => 'loggly',
                'svg' => true,
                'uri' => 'https://www.loggly.com/',
            ],
            [
                'name' => 'The Naked Scientists',
                'filename' => 'naked-scientists',
                'svg' => false,
                'uri' => 'https://www.thenakedscientists.com/',
            ],
            [
                'name' => 'New Relic',
                'filename' => 'new-relic',
                'svg' => true,
                'uri' => 'https://newrelic.com/',
            ],
            [
                'name' => 'Publons',
                'filename' => 'publons',
                'svg' => true,
                'uri' => 'https://publons.com/',
            ],
            [
                'name' => 'Slack',
                'filename' => 'slack',
                'svg' => true,
                'uri' => 'https://slack.com/',
            ],
        ];

        $content = [
            [
                'name' => 'CLOCKSS',
                'filename' => 'clockss',
                'svg' => false,
                'uri' => 'https://www.clockss.org/',
            ],
            [
                'name' => 'Europe PubMed Central',
                'filename' => 'europe-pmc',
                'svg' => false,
                'uri' => 'https://europepmc.org/',
            ],
            [
                'name' => 'Go OA',
                'filename' => 'gooa',
                'svg' => false,
                'uri' => 'http://gooa.las.ac.cn/',
            ],
            [
                'name' => 'Jisc',
                'filename' => 'jisc',
                'svg' => true,
                'uri' => 'https://www.jisc.ac.uk/',
            ],
            [
                'name' => 'LOCKSS',
                'filename' => 'lockss',
                'svg' => true,
                'uri' => 'https://www.lockss.org/',
            ],
            [
                'name' => 'Mendeley',
                'filename' => 'mendeley',
                'svg' => true,
                'uri' => 'https://www.mendeley.com/',
            ],
            [
                'name' => 'Paperity',
                'filename' => 'paperity',
                'svg' => true,
                'uri' => 'http://paperity.org/',
            ],
            [
                'name' => 'PubMed Central',
                'filename' => 'pmc',
                'svg' => false,
                'uri' => 'https://www.ncbi.nlm.nih.gov/pmc/',
            ],
            [
                'name' => 'SHARE',
                'filename' => 'share',
                'svg' => true,
                'uri' => 'http://www.share-research.org/',
            ],
        ];

        $committees = [
            [
                'name' => 'Crossref',
                'filename' => 'crossref',
                'svg' => true,
                'uri' => 'https://www.crossref.org/',
            ],
            [
                'name' => 'FORCE11',
                'filename' => 'force11',
                'svg' => false,
                'uri' => 'https://www.force11.org/about/directors-and-advisors',
            ],
            [
                'name' => 'JATS for Reuse',
                'filename' => 'jats4r',
                'svg' => false,
                'uri' => 'http://jats4r.org/',
            ],
            [
                'name' => 'Open Access Scholarly Publishers Association',
                'filename' => 'oaspa',
                'svg' => true,
                'uri' => 'https://oaspa.org/',
            ],
            [
                'name' => 'Publons',
                'filename' => 'publons',
                'svg' => true,
                'uri' => 'https://publons.com/',
            ],
        ];

        $arguments['listings'] = [
            GridListing::forImageLinks($this->toImageLinks($memberships), 'Memberships'),
            GridListing::forImageLinks($this->toImageLinks($serviceProviders), 'Service providers'),
            GridListing::forImageLinks($this->toImageLinks($content), 'Content availability and archiving'),
            GridListing::forImageLinks($this->toImageLinks($committees), 'Committees and initiatives'),
        ];

        return new Response($this->get('templating')->render('::who-we-work-with.html.twig', $arguments));
    }

    private function toImageLinks(array $items) : array
    {
        return array_map(function (array $item) : ImageLink {
            $sources = [];
            if ($item['svg']) {
                $sources[] = ['srcset' => $this->get('assets.packages')->getUrl("assets/images/logos/{$item['filename']}.svg"), 'type' => 'image/svg+xml'];
            }
            $sources[] = ['srcset' => sprintf('%s 200w, %s 400w', $this->get('assets.packages')->getUrl("assets/images/logos/{$item['filename']}-lo-res.webp"), $this->get('assets.packages')->getUrl("assets/images/logos/{$item['filename']}-hi-res.webp")), 'type' => 'image/webp'];

            return new ImageLink(
                $item['uri'],
                new Picture(
                    $sources,
                    new Image(
                        $this->get('assets.packages')->getUrl("assets/images/logos/{$item['filename']}-lo-res.png"),
                        [
                            400 => $this->get('assets.packages')->getUrl("assets/images/logos/{$item['filename']}-hi-res.png"),
                        ],
                        $item['name']
                    )
                )
            );
        }, $items);
    }
}
