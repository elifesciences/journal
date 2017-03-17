<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Form\Type\LabsExperimentFeedbackType;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Humanizer;
use eLife\Journal\Helper\Paginator;
use eLife\Journal\Pagerfanta\SequenceAdapter;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\BackgroundImage;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\GridListing;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;
use eLife\Patterns\ViewModel\Teaser;
use Pagerfanta\Pagerfanta;
use Swift_Message;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $arguments = $this->defaultPageArguments();

        $experiments = promise_for($this->get('elife.api_sdk.labs_experiments'))
            ->then(function (Sequence $sequence) use ($page, $perPage) {
                $pagerfanta = new Pagerfanta(new SequenceAdapter($sequence, $this->willConvertTo(Teaser::class, ['variant' => 'grid'])));
                $pagerfanta->setMaxPerPage($perPage)->setCurrentPage($page);

                return $pagerfanta;
            });

        $arguments['title'] = 'Labs';

        $arguments['paginator'] = $experiments
            ->then(function (Pagerfanta $pagerfanta) use ($request) {
                return new Paginator(
                    'Browse our experiments',
                    $pagerfanta,
                    function (int $page = null) use ($request) {
                        $routeParams = $request->attributes->get('_route_params');
                        $routeParams['page'] = $page;

                        return $this->get('router')->generate('labs', $routeParams);
                    }
                );
            });

        $arguments['listing'] = $arguments['paginator']
            ->then($this->willConvertTo(GridListing::class, ['heading' => 'Experiments', 'type' => 'experiments']));

        if (1 === $page) {
            return $this->createFirstPage($arguments);
        }

        return $this->createSubsequentPage($request, $arguments);
    }

    private function createFirstPage(array $arguments) : Response
    {
        $arguments['contentHeader'] = ContentHeaderNonArticle::basic('eLife Labs', true, null, null, null,
            new BackgroundImage(
                $this->get('assets.packages')->getUrl('assets/images/banners/labs-lo-res.jpg'),
                $this->get('assets.packages')->getUrl('assets/images/banners/labs-hi-res.jpg')
            ));

        $arguments['leadParas'] = new LeadParas([
            new LeadPara('eLife Labs showcases experiments in new functionality and technologies. Some experiments may be
developed further to become features on the eLife platform.'),
            new LeadPara('Feedback welcome!'),
        ]);

        return new Response($this->get('templating')->render('::labs.html.twig', $arguments));
    }

    public function experimentAction(Request $request, int $number) : Response
    {
        $experiment = $this->get('elife.api_sdk.labs_experiments')
            ->get($number)
            ->otherwise($this->mightNotExist());

        $arguments = $this->defaultPageArguments($experiment);

        $arguments['title'] = $experiment
            ->then(Callback::method('getTitle'));

        $arguments['experiment'] = $experiment;

        $arguments['feedbackForm'] = $experiment
            ->then(function (LabsExperiment $experiment) use ($request) {
                $uri = $this->get('router')->generate('labs-experiment', ['number' => $experiment->getNumber()], UrlGeneratorInterface::ABSOLUTE_URL);

                /** @var FormInterface $form */
                $form = $this->get('form.factory')
                    ->create(LabsExperimentFeedbackType::class, null, ['action' => $uri]);

                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
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

                        throw new EarlyResponse(new RedirectResponse($uri));
                    }

                    foreach ($form->getErrors(true) as $error) {
                        $this->get('session')
                            ->getFlashBag()
                            ->add(InfoBar::TYPE_ATTENTION, $error->getMessage());
                    }
                }

                return ArticleSection::basic('Feedback', 2, $this->render($this->get('elife.journal.view_model.converter')->convert($form->createView())));
            });

        $arguments['contentHeader'] = $arguments['experiment']
            ->then($this->willConvertTo(ContentHeaderNonArticle::class));

        $arguments['leadParas'] = $arguments['experiment']
            ->then(Callback::methodEmptyOr('getImpactStatement', $this->willConvertTo(LeadParas::class)));

        $arguments['blocks'] = $arguments['experiment']
            ->then($this->willConvertContent());

        $response = new Response($this->get('templating')->render('::labs-experiment.html.twig', $arguments));

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
