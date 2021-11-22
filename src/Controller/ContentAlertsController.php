<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Form\Type\ContentAlertsUpdateRequestType;
use eLife\Journal\Guzzle\CiviCrmClient;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ButtonCollection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContentAlertsController extends Controller
{
    public function subscribeAction(Request $request, string $variant = null) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['emailCta'] = null;

        $arguments['title'] = 'Subscribe to eLife\'s email alerts';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, ['preferences' => $this->defaultPreferences($variant), 'variant' => $variant], ['action' => $variant ? $this->get('router')->generate('content-alerts-variant', ['variant' => $variant]) : $this->get('router')->generate('content-alerts')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form) {
                    // Check if user not found, opted out or not member of relevant groups.
                    return (empty($check) || $check['opt_out'] || empty($check['groups'])) ?
                        // Subscribe if true.
                        $this->get('elife.api_client.client.crm_api')
                            ->subscribe(
                                empty($check) ? $form->get('email')->getData() : $check['contact_id'],
                                $form->get('preferences')->getData(),
                                $this->generatePreferencesUrl(),
                                null,
                                null,
                                empty($check) ? null : $check['preferences']
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
                        // Send preferences link if false.
                        $this->get('elife.api_client.client.crm_api')
                            ->triggerPreferencesEmail($check['contact_id'], empty($check['preferences_url']) ? $this->generatePreferencesUrl() : null)
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
                    throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('content-alerts-link-expired')));
                }

                return $check;
            })
            ->wait();

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, $data, ['action' => $this->get('router')->generate('content-alerts-update', ['id' => $id])]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form) {
                    // Check if user not found, opted out or not member of relevant groups.
                    return (empty($check) || $check['opt_out'] || empty($check['groups'])) ?
                        // Subscribe if true.
                        $this->get('elife.api_client.client.crm_api')
                            ->subscribe(
                                empty($check) ? $form->get('email')->getData() : $check['contact_id'],
                                $form->get('preferences')->getData(),
                                $this->generatePreferencesUrl(),
                                null,
                                null,
                                empty($check) ? null : $check['preferences']
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
                        // Send preferences link if false.
                        $this->get('elife.api_client.client.crm_api')
                            ->triggerPreferencesEmail($check['contact_id'], empty($check['preferences_url']) ? $this->generatePreferencesUrl() : null)
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

    public function linkExpiredAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['emailCta'] = null;

        $arguments['title'] = 'Your email preferences';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsUpdateRequestType::class, null, ['action' => $this->get('router')->generate('content-alerts-link-expired')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form) {
                    return !empty($check) ? $this->get('elife.api_client.client.crm_api')
                        ->triggerPreferencesEmail($check['contact_id'], empty($check['preferences_url']) ? $this->generatePreferencesUrl() : null)
                        ->then(function () use ($form) {
                            return ArticleSection::basic(
                                'Thank you',
                                2,
                                $this->render(
                                    new Paragraph("An email has been sent to <strong>{$form->get('email')->getData()}</strong>. Please follow the link in the email to update your preferences."),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                                ),
                                'thank-you'
                            );
                        }) : ArticleSection::basic(
                            'Something went wrong',
                            2,
                            $this->render(
                                new Paragraph("<strong>{$form->get('email')->getData()}</strong> is not subscribed to email alerts. Please try entering your email address again if you made an error."),
                                new ButtonCollection([
                                    Button::link('Try again', $this->get('router')->generate('content-alerts-link-expired')),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home')),
                                ])
                            ),
                            'try-again'
                        );
                })->wait();
        }, false);

        if ($validSubmission instanceof ArticleSection) {
            $arguments['form'] = $validSubmission;
        } else {
            $arguments['formIntro'] = ArticleSection::basic(
                'Your link has expired',
                2,
                $this->render(
                    new Paragraph('Please provide your email address and we will send you an email with a link to update your preferences.')
                ),
                'expired'
            );
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    private function defaultPreferences(string $variant = null) : array
    {
        switch ($variant) {
            case 'early-career':
                return [CiviCrmClient::LABEL_EARLY_CAREER];
            case 'technology':
                return [CiviCrmClient::LABEL_TECHNOLOGY];
            case 'elife-newsletter':
                return [CiviCrmClient::LABEL_ELIFE_NEWSLETTER];
            default:
                return [CiviCrmClient::LABEL_LATEST_ARTICLES];
        }
    }

    private function generatePreferencesUrl(string $id = null) : string
    {
        return $this->get('router')->generate('content-alerts-update', ['id' => $id ?? uniqid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
