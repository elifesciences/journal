<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Exception\ResponseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class SubjectsController extends Controller
{
    public function subjectAction(string $id) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['subject'] = $this->get('elife.api_sdk.subjects')->getSubject(1, $id)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof ResponseException && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Subject not found', $e);
                }
            });

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
