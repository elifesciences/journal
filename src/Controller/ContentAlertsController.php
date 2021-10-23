<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Form\Type\ContentAlertsUpdateRequestType;
use eLife\Journal\Guzzle\CiviCrmClient;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\InfoBar;
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
        $arguments['emailCta'] = null;

        $arguments['emailCta'] = null;

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
                            $form->get('preferences')->getData(),
                            $this->generatePreferencesUrl()
                        )
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'Thank you for subscribing!',
                                2,
                                $this->render(
                                    new Paragraph("A confirmation email has been sent to <strong>{$form->get('email')->getData()}</strong>."),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                                ),
                                'thank-you'
                            );
                        }) :
                        $this->get('elife.api_client.client.crm_api')
                        ->triggerPreferencesEmail($check['contact_id'])
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'You are already subscribed',
                                2,
                                $this->render(
                                    new Paragraph("An email has been sent to <strong>{$form->get('email')->getData()}</strong>."),
                                    new Paragraph('Please follow the link in your email to update your preferences.'),
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

        $arguments['emailCta'] = null;

        $arguments['title'] = 'Your email preferences';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        $data = $this->get('elife.api_client.client.crm_api')
            ->checkSubscription($this->generatePreferencesUrl($id), true)
            ->then(function ($check) {
                if (!$check) {

                    $this->get('session')
                        ->getFlashBag()
                        ->add(InfoBar::TYPE_WARNING,
                            'The preferences link that you followed has expired.');
                    throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('content-alerts-update-request')));
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
                    $this->generatePreferencesUrl(),
                    $form->get('first_name')->getData(),
                    $form->get('last_name')->getData(),
                    explode(',', $form->get('groups')->getData())
                )
                ->then(function () use ($form) {
                    return ArticleSection::basic(
                        'Thank you',
                        2,
                        $this->render(
                            new Paragraph("Email preferences for <strong>{$form->get('email')->getData()}</strong> have been updated."),
                            Button::link('Back to Homepage', $this->get('router')->generate('home'))
                        ),
                        'thank-you'
                    );
                })->wait();
        }, false);

        if ($validSubmission instanceof ArticleSection) {
            $arguments['form'] = $validSubmission;
        } else {
            $arguments['formIntro'] = new Paragraph("Change which email alerts you receive from eLife. Emails will be sent to <strong>{$form->get('email')->getData()}</strong>.");
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function updateRequestAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['emailCta'] = null;

        $arguments['title'] = 'Your email preferences';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsUpdateRequestType::class, null, ['action' => $this->get('router')->generate('content-alerts-update-request')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form) {
                    if (!empty($check)) {
                        return empty($check['preferences_url']) ? $this->get('elife.api_client.client.crm_api')
                            ->storePreferencesUrl($check['contact_id'], $this->generatePreferencesUrl()) :
                            ['contact_id' => $check['contact_id']];
                    }
                })->then(function ($data) use ($form) {
                    dump($data);
                    return !empty($data) ? $this->get('elife.api_client.client.crm_api')
                        ->triggerPreferencesEmail($data['contact_id'])
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'Nearly there',
                                2,
                                $this->render(
                                    new Paragraph("An email has been sent to <strong>{$form->get('email')->getData()}</strong>."),
                                    new Paragraph('Please follow the link in your email to update your preferences.'),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                                ),
                                'thank-you'
                            );
                        }) : ArticleSection::basic(
                            'Your email address is not recognised',
                            2,
                            $this->render(
                                Button::link('Sign up for email alerts', $this->get('router')->generate('content-alerts'))
                            ),
                            'thank-you'
                        );
                })->wait();
        }, false);

        if ($validSubmission instanceof ArticleSection) {
            $arguments['form'] = $validSubmission;
        } else {
            $arguments['formIntro'] = new Paragraph("Please provide your email address and we will send you an email with a link to update your preferences.");
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    private function generatePreferencesUrl(string $id = null) : string
    {
        return $this->get('router')->generate('content-alerts-update', ['id' => $id ?? uniqid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
