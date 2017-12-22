<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Profile;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Journal\ViewModel\EmptyListing;
use eLife\Patterns\ViewModel\AnnotationTeaser;
use eLife\Patterns\ViewModel\ContentHeaderProfile;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Promise\promise_for;

final class ProfilesController extends Controller
{
    public function profileAction(Request $request, string $id) : Response
    {
        if (!$this->isGranted('FEATURE_CAN_VIEW_PROFILES')) {
            throw new NotFoundHttpException('Not found');
        }

        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $profile = $this->get('elife.api_sdk.profiles')
            ->get($id)
            ->otherwise($this->mightNotExist());

        $annotations = promise_for($this->get('elife.api_sdk.annotations')->list($id))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(AnnotationTeaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments = $this->defaultPageArguments($request, $profile);

        $arguments['title'] = $profile
            ->then(function (Profile $profile) {
                return $profile->getDetails()->getPreferredName();
            });

        $arguments['profile'] = $profile;

        $arguments['contentHeader'] = $arguments['profile']
            ->then(function (Profile $profile) use ($arguments) {
                if ($arguments['user'] && $profile->getId() === $arguments['user']->getUsername()) {
                    $isUser = true;
                }

                return $this->convertTo($profile, ContentHeaderProfile::class, ['isUser' => $isUser ?? false]);
            });

        $arguments['paginator'] = $annotations
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse annotations',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('profile', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then(function (Paginator $paginator) {
                // TODO replace with listing pattern

                if (0 === count($paginator->getItems())) {
                    return [new EmptyListing('Annotations', 'No annotations available.')];
                }

                return $paginator->getItems();
            });

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        return new Response($this->get('templating')->render('::profile.html.twig', $arguments));
    }
}
