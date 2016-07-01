<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ArchiveController extends Controller
{
    public function yearAction(int $year) : Response
    {
        if ($year < 2012) {
            throw new NotFoundHttpException('eLife did not publish in '.$year);
        } elseif ($year >= date('Y')) {
            throw new NotFoundHttpException('Year not yet in archive');
        }

        $arguments = $this->defaultPageArguments();

        $arguments['year'] = $year;

        return new Response($this->get('templating')->render('::archive-year.html.twig', $arguments));
    }
}
