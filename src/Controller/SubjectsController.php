<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Exception\ResponseException;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
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

        $arguments['lead_paras'] = $arguments['subject']
            ->then(function (Result $result) {
                return new LeadParas([new LeadPara($result['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
