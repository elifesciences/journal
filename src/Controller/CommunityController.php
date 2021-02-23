<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\HasPages;
use eLife\Journal\Helper\Paginator;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommunityController extends Controller
{
    use HasPages;

    public function listAction(Request $request) : Response
    {
        $page = (int) $request->query->get('page', 1);
        $perPage = 10;
        $arguments = $this->defaultPageArguments($request);

        $latestCommunity = $this->pagerfantaPromise(
            $this->get('elife.api_sdk.community'),
            $page,
            $perPage,
            $this->willConvertTo(Teaser::class)
        );

        $arguments['title'] = 'Community';

        $arguments['paginator'] = $this->paginator(
            $latestCommunity,
            $request,
            'Browse our community articles',
            'community'
        );

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(ListingTeasers::class, ['emptyText' => 'No community articles available.']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = new ContentHeader(
            'Community',
            $this->get('elife.journal.view_model.factory.content_header_image')->forLocalFile('community'),
            'The eLife community is working to help address some of the pressures on early-career scientists in a number of ways.
Learn more about <a href="'.$this->get('router')->generate('about-research-culture').'">our work</a> and <a href="'.$this->get('router')->generate('about-people', ['type' => 'early-career']).'">advisory group</a>, <a href="https://crm.elifesciences.org/crm/community-news">sign up for our monthly news</a>,
follow us on <a href="https://www.twitter.com/elifecommunity">Twitter</a>, and explore recent activities below.'
        );

        $arguments['highlights'] = (new PromiseSequence($this->get('elife.api_sdk.highlights')
            ->get('community')
            ->slice(0, 6)))
            ->then(Callback::emptyOr(function (Sequence $result) {
                return ListingTeasers::basic($result->map($this->willConvertTo(Teaser::class, ['variant' => 'secondary']))->toArray(), new ListHeading('Highlights'));
            }))
            ->otherwise($this->softFailure('Failed to load community highlights'));

        return new Response($this->get('templating')->render('::community.html.twig', $arguments));
    }
}
