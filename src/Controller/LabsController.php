<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;

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

        $arguments['experiments'] = $this->get('elife.api_sdk.labs_experiments')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $experiments) {
                if ($experiments->isEmpty()) {
                    return null;
                }

                return GridListing::forTeasers($experiments->map(function (LabsExperiment $experiment) {
                    return $this->get('elife.journal.view_model.converter')->convert($experiment, Teaser::class, ['variant' => 'grid']);
                })->toArray(), 'Experiments');
            });

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function experimentAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['experiment'] = $this->get('elife.api_sdk.labs_experiments')->get($number);

        $arguments['contentHeader'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return $this->get('elife.journal.view_model.converter')->convert($experiment, ContentHeaderNonArticle::class);
            });

        $arguments['leadParas'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return new LeadParas([new LeadPara($experiment->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['experiment']
            ->then(function (LabsExperiment $experiment) {
                return $experiment->getContent()->map(function (Block $block) {
                    return $this->get('elife.journal.view_model.converter')->convert($block);
                });
            });

        return new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));
    }
}
