<?php

namespace eLife\Journal\Controller;

use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Code;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\IFrame;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
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
        ];

        return new Response($this->get('templating')->render('::resources.html.twig', $arguments));
    }
}
