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
            ArticleSection::basic('Posters', 2, $this->render(
                new Paragraph('Ready to download, print, and use, the posters below have been created to spread the word about our initiatives for early-career researchers.'),
                Listing::unordered([
                    '$1,000 Travel grants (2017) – <a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/downloads/a4-elife-travel-grants.pdf').'">A4 download</a> | <a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/downloads/letter-elife-travel-grants.pdf').'">US&nbsp;letter download</a>',
                    '#ECRWednesday Webinars – <a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/downloads/a4-elife-ecr-wednesdays.pdf').'">A4 download</a> | <a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/downloads/letter-elife-ecr-wednesdays.pdf').'">US&nbsp;letter download</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Presentations', 2, $this->render(
                new Paragraph('The team at eLife present at meetings across the globe, discussing the key challenges, opportunities and solutions to improve research communication and discovery. These presentations are available to view and share through our Figshare channel.'),
                Listing::unordered([
                    '<a href="https://figshare.com/authors/eLife_Science/4385029">Visit Figshare</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Videos', 2, $this->render(
                new Paragraph('We’ve used video as a medium to discuss topics such as the peer review process, challenges for early-career researchers and for our editors to share their insights. All are available to share and embed from our YouTube channel.'),
                Listing::unordered([
                    '<a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">Visit YouTube channel</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('Images', 2, $this->render(
                new Paragraph('A picture is worth a thousand words and we use imagery to create impact and capture the imagination of our readers. In our Flickr channel you’ll find images carefully selected from research articles or specifically created, available to use under the CC BY 2.0 licensing agreement.'),
                Listing::unordered([
                    '<a href="https://www.flickr.com/photos/128643624@N07/">Visit Flickr channel</a>',
                ], 'bullet')
            )),
            ArticleSection::basic('The eLife logo', 2, $this->render(
                new Paragraph('The eLife logo is available to download below. Please refer to the brand guide for usage guidelines and ensure you are downloading the correct file type for your purpose.'),
                ArticleSection::basic('Full-colour horizontal', 3, $this->render(
                    Listing::unordered([
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.eps').'">Download .eps for print</a>',
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.jpg').'">Download .jpg for web</a>',
                        '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.png').'">Download .png with transparent background</a>',
                    ], 'bullet')
                ))
            )),
            ArticleSection::basic('Brand guide', 2, $this->render(
                new Paragraph('The eLife brand guide has been developed to visualise our brand identity and offer guidance to our partners when working with us. We ask that you follow these guidelines when using the eLife name, logo or brand identity.'),
                Listing::unordered([
                    '<a href="'.$this->generateDownloadLink('https://cdn.elifesciences.org/images/eLife-brand-guide.pdf').'">Download PDF</a>',
                ], 'bullet')
            )),
        ];

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }

    private function generateDownloadLink(string $uri) : string
    {
        return $this->get('elife.journal.helper.download_link_uri_generator')->generate(DownloadLink::fromUri($uri));
    }
}
