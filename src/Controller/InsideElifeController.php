<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Identifier;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use eLife\Patterns\ViewModel\SpeechBubble;
use eLife\Patterns\ViewModel\Teaser;
use function GuzzleHttp\Promise\all;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\promise_for;

final class InsideElifeController extends Controller
{
    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;

        $arguments = $this->defaultPageArguments($request);

        $latest = promise_for($this->get('elife.api_sdk.blog_articles'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class)));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Inside eLife';

        $arguments['paginator'] = $latest
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse Inside eLife',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('inside-elife', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['heading' => 'Latest', 'emptyText' => 'No articles available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        return new Response($this->get('templating')->render('::inside-elife.html.twig', $arguments));
    }

    public function articleAction(Request $request, string $id) : Response
    {
        $arguments['item'] = $this->get('elife.api_sdk.blog_articles')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $arguments['item']);
        
        $arguments['hasSocialMedia'] = true;

        $arguments['title'] = $arguments['item']
            ->then(Callback::method('getTitle'));

        $arguments['pageViews'] = $this->get('elife.api_sdk.metrics')
            ->totalPageViews(Identifier::blogArticle($id))
            ->otherwise($this->mightNotExist())
            ->otherwise($this->softFailure('Failed to load page views count'));

        $arguments['contextualDataMetrics'] = all(['pageViews' => $arguments['pageViews']])
            ->then(function (array $parts) {
                /** @var int|null $pageViews */
                $pageViews = $parts['pageViews'];

                $metrics = [];

                if (null !== $pageViews && $pageViews > 0) {
                    $metrics[] = sprintf('<span class="contextual-data__counter">%s</span> %s', number_format($pageViews), 'views');
                }

                return $metrics;
            });

        $arguments['contentHeader'] = all(['item' => $arguments['item'], 'metrics' => $arguments['contextualDataMetrics']])
            ->then(function (array $parts) {
                return $this->convertTo($parts['item'], ContentHeaderNew::class, ['metrics' => $parts['metrics']]);
            });

        $arguments['blocks'] = $arguments['item']
            ->then($this->willConvertContent());
        
        $arguments['socialMediaSharersLinks'] = all(['item' =>  $arguments['item']])
            ->then(function (array $parts) {
                return $this->convertTo($parts['item'], SocialMediaSharersNew::class);
            });

        return new Response($this->get('templating')->render('::inside-elife-article.html.twig', $arguments));
    }
}
