<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Helper\DownloadLink;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResourcesController extends Controller
{
    public function resourcesAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Resources';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, 'A collection of resources, from posters, videos, images, presentations and more, to the brand behind eLife.');

        $arguments['body'] = [
            ArticleSection::basic($this->render(
                new Paragraph('The team at eLife present at meetings across the globe, discussing the key challenges, opportunities and solutions to improve research communication and discovery. These presentations are available to view and share through our Figshare channel.'),
                Listing::unordered([
                    '<a href="https://figshare.com/authors/eLife_Science/4385029">Visit Figshare</a>',
                ], 'bullet')
            ), 'Presentations', 2),
            ArticleSection::basic($this->render(
                new Paragraph('We’ve used video as a medium to discuss topics such as the peer review process, challenges for early-career researchers and for our editors to share their insights. All are available to share and embed from our YouTube channel.'),
                Listing::unordered([
                    '<a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">Visit YouTube channel</a>',
                ], 'bullet')
            ), 'Videos', 2),
            ArticleSection::basic($this->render(
                new Paragraph('A picture is worth a thousand words and we use imagery to create impact and capture the imagination of our readers. In our Flickr channel you’ll find images carefully selected from research articles or specifically created, available to use under the CC BY 2.0 licensing agreement.'),
                Listing::unordered([
                    '<a href="https://www.flickr.com/photos/109374423@N04/albums">Visit Flickr channel</a>',
                ], 'bullet')
            ), 'Images', 2),
            ArticleSection::basic($this->render(
                new Paragraph('The eLife logo is available to download below. Please ensure you are downloading the correct file type for your purpose.'),
                ArticleSection::basic($this->render(
                    Listing::unordered([
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal-2020.eps').'">Download .eps for print</a>',
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal-2020.jpg').'">Download .jpg for web</a>',
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal-2020.png').'">Download .png with transparent background</a>',
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal-2020.svg').'">Download .svg for web</a>',
                    ], 'bullet')
                ), 'Full-colour horizontal', 3),
                ArticleSection::basic($this->render(
                    Listing::unordered([
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/Downloads/eLife-logo-guide.pdf').'">Download .pdf for logo guide</a>',
                    ], 'bullet')
                ), 'Logo guide', 3)
            ), 'The eLife logo', 2),
        ];

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }

    private function generateDownloadLink(string $uri) : string
    {
        return $this->get('elife.journal.helper.download_link_uri_generator')->generate(DownloadLink::fromUri($uri));
    }
}
