<?php

namespace eLife\Journal\Controller;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\Exception\BadResponse;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\GridListing;
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

        $arguments['experiments'] = $this->get('elife.api_client.labs')
            ->listExperiments(['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT_LIST, 1)], $page, $perPage)
            ->then(function (Result $result) {
                if (empty($result['items'])) {
                    return null;
                }

                return GridListing::forTeasers(array_map(function (array $experiment) {
                    return $this->get('elife.journal.view_model.factory.teaser_grid')
                        ->forExperiment($experiment);
                }, $result['items']), 'Experiments');
            });

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function experimentAction(int $number) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['experiment'] = $this->get('elife.api_client.labs')
            ->getExperiment(['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)], $number)
            ->otherwise(function (Throwable $e) {
                if ($e instanceof BadResponse && 404 === $e->getResponse()->getStatusCode()) {
                    throw new NotFoundHttpException('Experiment not found', $e);
                }
            });

        $arguments['contentHeader'] = $arguments['experiment']
            ->then(function (Result $experiment) {
                return ContentHeaderNonArticle::basic($experiment['title'], false, null, null,
                    Meta::withText('Experiment: '.str_pad($experiment['number'], 3, '0', STR_PAD_LEFT),
                        new Date(DateTimeImmutable::createFromFormat(DATE_ATOM, $experiment['published']))),
                    new BackgroundImage(
                        $experiment['image']['banner']['sizes']['2:1'][900],
                        $experiment['image']['banner']['sizes']['2:1'][1800]
                    )
                );
            });

        $arguments['leadParas'] = $arguments['experiment']
            ->then(function (Result $experiment) {
                return new LeadParas([new LeadPara($experiment['impactStatement'])]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['blocks'] = $arguments['experiment']
            ->then(function (Result $experiment) {
                return $this->get('elife.website.view_model.block_converter')->handleBlocks(...$experiment['content']);
            });

        return new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));
    }
}
