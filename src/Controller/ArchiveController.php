<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\Select;
use eLife\Patterns\ViewModel\SelectNav;
use eLife\Patterns\ViewModel\SelectOption;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ArchiveController extends Controller
{
    public function indexAction(Request $request) : Response
    {
        $year = (int) $request->query->get('year');

        if ($year < 2012 || $year >= date('Y')) {
            throw new NotFoundHttpException();
        }

        return new RedirectResponse(
            $this->get('router')->generate('archive-year', ['year' => $year]),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    public function yearAction(int $year) : Response
    {
        if ($year < 2012) {
            throw new NotFoundHttpException('eLife did not publish in '.$year);
        } elseif ($year >= date('Y')) {
            throw new NotFoundHttpException('Year not yet in archive');
        }

        $arguments = $this->defaultPageArguments();

        $years = [];
        for ($yearOption = 2012; $yearOption < date('Y'); ++$yearOption) {
            $years[] = new SelectOption($yearOption, $yearOption, $yearOption === $year);
        }

        $arguments['contentHeader'] = ContentHeaderNonArticle::archive(
            'Monthly archive',
            false,
            new SelectNav(
                $this->get('router')->generate('archive'),
                new Select('year', $years, new FormLabel('Archive year', 'year', true)),
                Button::form('Go', Button::TYPE_SUBMIT, Button::SIZE_EXTRA_SMALL)
            )
        );

        $arguments['year'] = $year;

        return new Response($this->get('templating')->render('::archive-year.html.twig', $arguments));
    }
}
