<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiSdk\Exception\ResponseException;
use eLife\ApiSdk\Result;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class LabsController extends Controller
{
    public function listAction() : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife Labs');

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('eLife Labs showcases experiments in new functionality and technologies. Some experiments may be
developed further to become features on the eLife platform.'),
            new LeadPara('Feedback welcome!'),
        ]);

        $arguments['experiments'] = $this->get('elife.api_sdk.labs')->listExperiments(1, $page, $perPage)
            ->then(function (Result $result) {
                $teasers = [];

                foreach ($result['items'] as $experiment) {
                    $teasers[] = $this->get('elife.journal.view_model.factory.teaser_non_article_content')
                        ->forExperiment($experiment);
                }

                return $teasers;
            });

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function experimentAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['experiment'] = $this->get('elife.api_sdk.labs')->getExperiment(1, $number)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof ResponseException && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Experiment not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['experiment']
            ->then(function (Result $experiment) {
                return ContentHeaderNonArticle::basic($experiment['title'], false, null, null,
                    Meta::withText('Experiment: '.str_pad($experiment['number'], 3, '0', STR_PAD_LEFT),
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $experiment['published'])))
                );
            });

        return new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));
    }
}
