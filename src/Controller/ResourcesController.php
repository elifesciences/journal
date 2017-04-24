<?php

namespace eLife\Journal\Controller;

use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Code;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\IFrame;
use eLife\Patterns\ViewModel\Image;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Picture;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResourcesController extends Controller
{
    public function resourcesAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Resources';

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic($arguments['title']);

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('A collection of posters, handouts, slide presentations, videos, and more, about all of the work behind the eLife initiative.'),
        ]);

        $arguments['body'] = [
            new Paragraph('Everything we produce is made available under a <a href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution</a> (CC-BY) license,
so you are free to use it without asking permission so long as the author (eLife, for the materials below) is given credit.
This is true for journal articles and related content. See our <a href="'.$this->get('router')->generate('terms').'">Terms and Conditions</a> for detail.'),
            ArticleSection::basic('Videos', 2, $this->render(
                new Paragraph('eLife peer review: The author&apos;s perspective'),
                new IFrame('https://www.youtube.com/embed/3pjXfgeOCho', 560, 315),
                new Code('<iframe width="560" height="315" src="https://www.youtube.com/embed/3pjXfgeOCho" frameborder="0" allowfullscreen></iframe>'),
                new Paragraph('The importance of eLife: Perspectives from the editors'),
                new IFrame('https://www.youtube.com/embed/videoseries?list=PLOAy5WJPezEi1bxU3Jxa82GBZwK9Xj982', 560, 315),
                new Code('<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLOAy5WJPezEi1bxU3Jxa82GBZwK9Xj982" frameborder="0" allowfullscreen></iframe>'),
                new Paragraph('eLife: Changing the review process'),
                new IFrame('https://www.youtube.com/embed/quCG17jZW-w', 560, 315),
                new Code('<iframe width="560" height="315" src="https://www.youtube.com/embed/quCG17jZW-w" frameborder="0" allowfullscreen></iframe>'),
                new Paragraph('All of our videos are available on <a href="https://www.youtube.com/channel/UCNEHLtAc_JPI84xW8V4XWyw">YouTube</a>.')
            )),
            ArticleSection::basic('Posters', 2, $this->render(
                $this->toStyleGuideImage('$1000 Travel Grants now available', 'travel-grants-thumbnail.png'),
                new Paragraph('$1,000 Travel grants (2017)'),
                new Paragraph('<a href="https://cdn.elifesciences.org/downloads/a4-elife-travel-grants.pdf">A4 download</a> |
<a href="https://cdn.elifesciences.org/downloads/letter-elife-travel-grants.pdf">US letter download</a> '),
                $this->toStyleGuideImage('#ECRWednesday Webinars - cultivate your career', 'ecr-wednesdays-thumbnail.png'),
                new Paragraph('#ECRWednesday Webinars - cultivate your career'),
                new Paragraph('<a href="https://cdn.elifesciences.org/downloads/a4-elife-ecr-wednesdays.pdf ">A4 download</a> |
<a href="https://cdn.elifesciences.org/downloads/letter-elife-ecr-wednesdays.pdf">US letter download</a> ')
            )),
            ArticleSection::basic('The eLife Logo', 2, $this->render(
                new Paragraph('The eLife logo may not be used to promote any organizations other than eLife, or to sell or promote any good or service without the express approval of eLife. If you have any questions, please <a href="'.$this->get('router')->generate('contact').'">contact us</a>.'),
                ArticleSection::basic('Acceptable Configurations', 3, $this->render(
                    new Paragraph('There are two possible configurations of the eLife logo. The logo may not be used in any other configuration.'),
                    $this->toStyleGuideImage('Acceptable Configurations', 'accept-config.jpg'),
                    new Paragraph('If colors are not being used, the logo may be presented at 100% black, or 60% black. On a dark background, the logo may <strong>only</strong> appear in reverse white.')
                )),
                ArticleSection::basic('Acceptable Color Use', 3, $this->render(
                    $this->toStyleGuideImage('Acceptable Color Use', 'accept-color.jpg')
                )),
                ArticleSection::basic('Downloadable logos', 3, $this->render(
                    new Paragraph('Please be sure you are downloading the correct logo for your purpose (i.e. for print, download CMYK, for web, download RGB).'),
                    ArticleSection::basic('Full-Color Horiztonal (Preferred)', 4, $this->render(
                        $this->toStyleGuideImage('Full-Color Horiztonal', 'elife-full-color-horizontal-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.eps">.eps</a> Web - RGB: <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.jpg">.jpg</a> (72dpi) <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-horizontal.png">.png</a> (72dpi with transparent background)')
                    )),
                    ArticleSection::basic('Full-Color Vertical', 4, $this->render(
                        $this->toStyleGuideImage('Full-Color Vertical', 'elife-full-color-vertical-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-vertical.eps">.eps</a> Web - RGB: <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-vertical.jpg">.jpg</a> (72dpi) <a href="https://cdn.elifesciences.org/style-guide-images/elife-full-color-vertical.png">.png</a> (72dpi with transparent background)')
                    )),
                    ArticleSection::basic('Greyscale Horizontal', 4, $this->render(
                        $this->toStyleGuideImage('Greyscale Horizontal', 'elife-greyscale-horizontal-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-horizontal.eps">.eps</a> Web - RGB: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-horizontal.jpg">.jpg</a> (72dpi) <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-horizontal.png">.png</a> (72dpi with transparent background)')
                    )),
                    ArticleSection::basic('Greyscale Horizontal (Reversed)', 4, $this->render(
                        $this->toStyleGuideImage('Greyscale Horizontal Reversed', 'elife-greyscale-horizontal-reversed-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-horizontal-reversed.eps">.eps</a> Web - RGB <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-horizontal-reversed.png">.png</a> (72dpi with transparent background)')
                    )),
                    ArticleSection::basic('Greyscale Vertical', 4, $this->render(
                        $this->toStyleGuideImage('Greyscale Vertical', 'elife-greyscale-vertical-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-vertical.eps">.eps</a> Web - RGB: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-vertical.jpg">.jpg</a> (72dpi) <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-vertical.png">.png</a> (72dpi with transparent background)')
                    )),
                    ArticleSection::basic('Greyscale Vertical (Reversed)', 4, $this->render(
                        $this->toStyleGuideImage('Greyscale Vertical Reversed', 'elife-greyscale-vertical-reversed-sm.jpg'),
                        new Paragraph('Print - CMYK: <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-vertical-reversed.eps">.eps</a> Web - RGB <a href="https://cdn.elifesciences.org/style-guide-images/elife-greyscale-vertical-reversed.png">.png</a> (72dpi with transparent background)')
                    ))
                ))
            )),
        ];

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }

    private function toStyleGuideImage($name, $filename) : Picture
    {
        $styleGuideDirectoryUri = 'https://cdn.elifesciences.org/style-guide-images/';
        $sourceUri = $styleGuideDirectoryUri.$filename;
        $sources = [];
        $sources[] = ['srcset' => sprintf('%s 200w', $sourceUri), 'type' => 'image/png'];

        return new Picture(
                $sources,
                new Image(
                    $sourceUri,
                    [],
                    $name,
                    []
                )
            );
    }
}
