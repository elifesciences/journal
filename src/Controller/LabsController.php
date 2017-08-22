<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsPost;
use eLife\Journal\Form\Type\LabsPostFeedbackType;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Humanizer;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Swift_Message;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
Learn more about <a href="'.$this->get('router')->generate('about-innovation').'">innovation at eLife</a>, or follow us on <a href="https://twitter.com/eLifeInnovation">Twitter</a>.'
        );

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function postAction(Request $request, string $id) : Response
    {
        $post = $this->get('elife.api_sdk.labs_posts')
            ->get($id)
            ->otherwise($this->mightNotExist())
            ->then($this->checkSlug($request, Callback::method('getTitle')));

        $arguments = $this->defaultPageArguments($request, $post);

        $arguments['title'] = $post
            ->then(Callback::method('getTitle'));

        $arguments['post'] = $post;

        $arguments['feedbackForm'] = $post
            ->then(function (LabsPost $post) use ($request) {
                $uri = $this->get('router')->generate('labs-post', [$post], UrlGeneratorInterface::ABSOLUTE_URL);

                /** @var FormInterface $form */
                $form = $this->get('form.factory')
                    ->create(LabsPostFeedbackType::class, null, ['action' => $uri]);

                $this->ifFormSubmitted($request, $form, function () use ($form, $uri) {
                    $this->get('session')
                        ->getFlashBag()
                        ->add(InfoBar::TYPE_SUCCESS,
                            'Thanks '.$form->get('name')->getData().', we have received your comment.');

                    $response = implode("\n\n", array_map(function (FormInterface $child) {
                        $label = ($child->getConfig()->getOption('label') ?? Humanizer::humanize($child->getName()));

                        return $label."\n".str_repeat('-', strlen($label))."\n".$child->getData();
                    }, array_filter(iterator_to_array($form), function (FormInterface $child) {
                        return !in_array($child->getConfig()->getType()->getBlockPrefix(), ['submit']);
                    })));

                    $message1 = Swift_Message::newInstance()
                        ->setSubject('Comment on eLife Labs')
                        ->setFrom('do_not_reply@elifesciences.org')
                        ->setTo($form->get('email')->getData(), $form->get('name')->getData())
                        ->setBody('Thanks for your comment. We will respond as soon as we can.

eLife Sciences Publications, Ltd is a limited liability non-profit non-stock corporation incorporated in the State of Delaware, USA, with company number 5030732, and is registered in the UK with company number FC030576 and branch number BR015634 at the address First Floor, 24 Hills Road, Cambridge CB2 1JP.');

                    $message2 = Swift_Message::newInstance()
                        ->setSubject('Comment submitted')
                        ->setFrom('do_not_reply@elifesciences.org')
                        ->setTo('labs@elifesciences.org')
                        ->setBody("A comment has been submitted on $uri\n\n$response");

                    $this->get('mailer')->send($message1);
                    $this->get('mailer')->send($message2);
                });

                return ArticleSection::basic('Feedback', 2, $this->render($this->get('elife.journal.view_model.converter')->convert($form->createView())));
            });

        $arguments['contentHeader'] = $arguments['post']
            ->then($this->willConvertTo(ContentHeader::class));

        $arguments['blocks'] = $arguments['post']
            ->then($this->willConvertContent());

        $response = new Response($this->get('templating')->render('::labs-post.html.twig', $arguments));

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
