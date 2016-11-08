<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\BlockLink;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\ListingTeasers;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\HttpFoundation\Response;

final class SubjectsController extends Controller
{
    public function listAction() : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('Browse our research categories');

        $arguments['subjects'] = $this->get('elife.api_sdk.subjects')
            ->reverse()
            ->slice(1, 100)
            ->map(function (Subject $subject) {
                return new BlockLink(
                    new Link(
                        $subject->getName(),
                        $this->get('router')->generate('subject', ['id' => $subject->getId()])
                    ),
                    new BackgroundImage(
                        $subject->getThumbnail()->getSize('16:9')->getImage(250),
                        $subject->getThumbnail()->getSize('16:9')->getImage(500),
                        600
                    )
                );
            })
            ->then(function (Sequence $subjects) {
                if ($subjects->isEmpty()) {
                    return null;
                }

                return GridListing::forBlockLinks($subjects->toArray());
            });

        return new Response($this->get('templating')->render('::subjects.html.twig', $arguments));
    }

    public function subjectAction(string $id) : Response
    {
        $page = 1;
        $perPage = 6;

        $arguments = $this->defaultPageArguments();

        $arguments['subject'] = $this->get('elife.api_sdk.subjects')->get($id);

        $arguments['contentHeader'] = $arguments['subject']
            ->then(function (Subject $subject) {
                return $this->get('elife.journal.view_model.converter')->convert($subject, ContentHeaderNonArticle::class);
            });

        $arguments['lead_paras'] = $arguments['subject']
            ->then(function (Subject $subject) {
                return new LeadParas([new LeadPara($subject->getImpactStatement())]);
            })
            ->otherwise(function () {
                return null;
            });

        $arguments['latestArticlesHeading'] = new ListHeading('Latest articles');
        $arguments['latestArticles'] = $this->get('elife.api_sdk.search')
            ->forSubject($id)
            ->forType('research-article', 'research-advance', 'research-exchange', 'short-report', 'tools-resources', 'replication-study', 'editorial', 'insight', 'feature', 'collection')
            ->sortBy('date')
            ->slice(($page * $perPage) - $perPage, $perPage)
            ->then(function (Sequence $result) use ($arguments) {
                if ($result->isEmpty()) {
                    return null;
                }

                return ListingTeasers::basic(
                    $result->map(function (Model $model) {
                        return $this->get('elife.journal.view_model.converter')->convert($model, Teaser::class);
                    })->toArray(),
                    $arguments['latestArticlesHeading']['heading']
                );
            });

        return new Response($this->get('templating')->render('::subject.html.twig', $arguments));
    }
}
