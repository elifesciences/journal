<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Guzzle\CiviCrmClient;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContentAlertsController extends Controller
{
    public function subscribeAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Subscribe to eLife\'s email alerts';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, ['preferences' => [CiviCrmClient::LABEL_LATEST_ARTICLES]], ['action' => $this->get('router')->generate('content-alerts')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form) {
                    return empty($check) ?
                        $this->get('elife.api_client.client.crm_api')
                        ->subscribe(
                            $form->get('email')->getData(),
                            $form->get('preferences')->getData()
                        )
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'Thank you for subscribing!',
                                2,
                                $this->render(new Paragraph("A confirmation email has been sent to <strong>{$form->get('email')->getData()}</strong>.")).$this->render(
                                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                                ),
                                'thank-you'
                            );
                        }) :
                        $this->get('elife.api_client.client.crm_api')
                        ->triggerPreferencesEmail($check['contact_id'], $this->get('router')->generate('content-alerts-update', ['id' => uniqid($check['contact_id'])], UrlGeneratorInterface::ABSOLUTE_URL))
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'Thank you for subscribing!',
                                2,
                                $this->render(new Paragraph("A link to update your preferences has been sent to <strong>{$form->get('email')->getData()}</strong>.")).$this->render(
                                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                                ),
                                'thank-you'
                            );
                        });
                })->wait();
        }, false);

        $arguments['form'] = $validSubmission instanceof ArticleSection ?
            $validSubmission :
            $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function updateAction(Request $request, string $id) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Subscribe to eLife\'s email alerts';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $data = $this->get('elife.api_client.client.crm_api')
            ->checkSubscription($this->get('router')->generate('content-alerts-update', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL), true)
            ->then(function ($check) {
                if (!$check) {
                    throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('content-alerts')));
                }

                return $check;
            })
            ->wait();

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, $data, ['action' => $this->get('router')->generate('content-alerts-update', ['id' => $id])]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->subscribe(
                    $form->get('contact_id')->getData(),
                    $form->get('preferences')->getData(),
                    $form->get('first_name')->getData(),
                    $form->get('last_name')->getData(),
                    explode(',', $form->get('groups')->getData())
                )
                ->then(function () use ($form) {
                    return ArticleSection::basic(
                        'Thank you for updating your preferences!',
                        2,
                        $this->render(
                            Button::link('Back to Homepage', $this->get('router')->generate('home'))
                        ),
                        'thank-you'
                    );
                })->wait();
        }, false);

        $arguments['form'] = $validSubmission instanceof ArticleSection ?
            $validSubmission :
            $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }
}
