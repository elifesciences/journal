<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsPost;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContextualData;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class LabsController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 8;

        $arguments = $this->defaultPageArguments($request);

        $posts = promise_for($this->get('elife.api_sdk.labs_posts'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class, ['variant' => 'grid'])));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Labs';

        $arguments['paginator'] = $posts
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our posts',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('labs', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(GridListing::class, ['heading' => 'Latest', 'type' => 'posts']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader(
            'eLife Labs',
            $this->get('elife.journal.view_model.factory.content_header_image')->forLocalFile('labs'),
            'Exploring open-source solutions at the intersection of research and technology.
Learn more about <a href="'.$this->get('router')->generate('about-innovation').'">innovation at eLife</a>, follow us on <a href="https://twitter.com/eLifeInnovation">Twitter</a>, or sign up for our <a href="https://crm.elifesciences.org/crm/tech-news?utm_source=Labs-home&utm_medium=website&utm_campaign=technews">technology and innovation newsletter</a>.'
        );

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function postAction(Request $request, string $id) : Response
    {
        $item = $this->get('elife.api_sdk.labs_posts')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $item);

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['contentHeader'] = $arguments['item']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['contextualData'] = $arguments['item']
            ->then($this->ifGranted(['FEATURE_CAN_USE_HYPOTHESIS'], function (LabsPost $post) {
                return ContextualData::annotationsOnly(SpeechBubble::forContextualData());
            }));

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent());

        if ($this->isGranted('FEATURE_CAN_USE_HYPOTHESIS')) {
            $arguments['speechBubble'] = SpeechBubble::forArticleBody();
        }

        $response = new Response($this->get('templating')->render('::labs-post.html.twig', $arguments));

        return $response;
    }
}
