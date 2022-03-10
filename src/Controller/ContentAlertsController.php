<?php

namespace eLife\Journal\Controller;

use eLife\CiviContacts\Etoc\EarlyCareer;
use eLife\CiviContacts\Etoc\ElifeNewsletter;
use eLife\CiviContacts\Etoc\LatestArticles;
use eLife\CiviContacts\Etoc\Newsletter;
use eLife\CiviContacts\Etoc\Subscription;
use eLife\CiviContacts\Etoc\Technology;
use eLife\CiviContacts\Guzzle\CiviCrmClient;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Form\Type\ContentAlertsOptoutType;
use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Form\Type\ContentAlertsUnsubscribeType;
use eLife\Journal\Form\Type\ContentAlertsUpdateRequestType;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ButtonCollection;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContentAlertsController extends Controller
{
    public function optoutAction(Request $request, string $id) : Response
    {
        $arguments = $this->simplePageArguments($request);

        $arguments['title'] = 'Opt-out of all newsletters';

        $formIntro = 'You will no longer receive regular updates from eLife.';

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(
                ContentAlertsOptoutType::class,
                null,
                [
                    'action' => $this->get('router')->generate('content-alerts-optout', ['id' => $id]),
                ]
            );

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form, &$arguments) {
            return $this->get('elife.api_client.client.crm_api')
                ->optout(
                    $form->get('contact_id')->getData(),
                    $form->get('reasons')->getData(),
                    $form->get('other')->getData()
                )
                ->then(function () use ($form, &$arguments) {
                    $this->get('elife.journal.google_client.opt_out_unsubscribe_reason')->record(
                        $form->get('reasons')->getData(),
                        $form->get('other')->getData(),
                        true
                    );
                    $arguments['title'] = 'Opt-out complete';
                    return [
                        new Paragraph('You will no longer receive regular updates from eLife.'),
                        Button::link('Back to Homepage', $this->get('router')->generate('home')),
                    ];
                })->wait();
        }, false);

        if (!$validSubmission && !$form->isSubmitted()) {
            /** @var Subscription $check */
            $check = $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($this->generateOptoutUrl($id), false, null, CiviCrmClient::FIELD_OPTOUT_URL)
                ->then(function ($check) use (&$arguments) {
                    if (!$check instanceof Subscription || $check->getOptout()) {
                        $arguments['title'] = 'Something went wrong';
                        return [
                            new Paragraph('Your email address has not been recognised. As a result, your email subscriptions have not been changed. Please try again or <a href="'.$this->get('router')->generate('contact').'">contact us</a>.'),
                            Button::link('Back to Homepage', $this->get('router')->generate('home')),
                        ];
                    }

                    return $check;
                })
                ->wait();

            if ($check instanceof Subscription) {
                if ($check->getPreferencesUrl()) {
                    $formIntro .= ' If you prefer to choose the emails you receive, please <a href="'.$check->getPreferencesUrl().'">update your preferences</a>.';
                }

                $form = ContentAlertsOptoutType::addContactId($form, $check->getId());
            } else {
                $form = $check;
            }
        }

        $arguments['contentHeader'] = new ContentHeaderSimple($arguments['title']);

        $arguments['formIntro'] = new Paragraph($formIntro);

        if ($validSubmission) {
            $arguments['form'] = $validSubmission;
        } elseif ($form instanceof FormInterface) {
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        } else {
            $arguments['form'] = $form;
        }

        if ($validSubmission || !$form instanceof FormInterface) {
            unset($arguments['formIntro']);
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function unsubscribeAction(Request $request, string $id, string $variant = null) : Response
    {
        $arguments = $this->simplePageArguments($request);

        $newsletters = $this->defaultPreferences($variant);

        $group = implode(', ', array_map(function (Newsletter $preference) {
            return $preference->description();
        }, $newsletters));

        $arguments['title'] = 'Unsubscribe from this mailing';

        $formIntro = [new Paragraph("You will no longer receive ${group}.")];

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(
                ContentAlertsUnsubscribeType::class,
                [
                    'groups' => implode(',', array_map(function (Newsletter $preference) {
                        return $preference->group();
                    }, $this->defaultPreferences($variant))),
                ],
                [
                    'action' => $variant ? $this->get('router')->generate('content-alerts-unsubscribe-variant', ['id' => $id, 'variant' => $variant]) : $this->get('router')->generate('content-alerts-unsubscribe', ['id' => $id]),
                ]
            );

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form, $newsletters, $group, &$arguments) {
            return $this->get('elife.api_client.client.crm_api')
                ->unsubscribe(
                    $form->get('contact_id')->getData(),
                    explode(',', $form->get('groups')->getData())
                )
                ->then(function () use ($form) {
                    return $this->get('elife.api_client.client.crm_api')
                        ->triggerUnsubscribeEmail($form->get('contact_id')->getData());
                })
                ->then(function () use ($form, $newsletters, $group, &$arguments) {
                    $this->get('elife.journal.google_client.opt_out_unsubscribe_reason')->record(
                        $form->get('reasons')->getData(),
                        $form->get('other')->getData(),
                        false,
                        $newsletters[0]
                    );
                    $arguments['title'] = 'Unsubscribed';
                    return [
                        new Paragraph("You are no longer subscribed to {$group}."),
                        Button::link('Back to Homepage', $this->get('router')->generate('home')),
                    ];
                })->wait();
        }, false);

        if (!$validSubmission && !$form->isSubmitted()) {
            /** @var Subscription $check */
            $check = $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($this->generateUnsubscribeUrl($id), false, $newsletters[0])
                ->then(function ($check) use (&$arguments, $newsletters) {
                    // If contact not found, have opted-out or has not subscribed to this newsletter then present "Something went wrong"
                    if (!$check instanceof Subscription || $check->getOptout() || empty(array_intersect($check->getPreferences(), $newsletters))) {
                        $arguments['title'] = 'Something went wrong';
                        return [
                            new Paragraph('Your email address has not been recognised. As a result, your email subscriptions have not been changed. Please try again or <a href="'.$this->get('router')->generate('contact').'">contact us</a>.'),
                            Button::link('Back to Homepage', $this->get('router')->generate('home')),
                        ];
                    }

                    return $check;
                })
                ->wait();

            if ($check instanceof Subscription) {
                if ($check->getPreferencesUrl()) {
                    $formIntro[] = new Paragraph('To change any other newsletter subscriptions, please <a href="'.$check->getPreferencesUrl().'">update your preferences</a>.');
                }

                $form = ContentAlertsUnsubscribeType::addContactId($form, $check->getId());
            } else {
                $form = $check;
            }
        }

        $arguments['contentHeader'] = new ContentHeaderSimple($arguments['title']);

        $arguments['formIntro'] = $formIntro;

        if ($validSubmission) {
            $arguments['form'] = $validSubmission;
        } elseif ($form instanceof FormInterface) {
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        } else {
            $arguments['form'] = $form;
        }

        if ($validSubmission || !$form instanceof FormInterface) {
            unset($arguments['formIntro']);
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function subscribeAction(Request $request, string $variant = null) : Response
    {
        $arguments = $this->simplePageArguments($request);

        $arguments['title'] = 'Subscribe to eLife\'s email alerts';

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(
                ContentAlertsType::class,
                [
                    'preferences' => array_map(function (Newsletter $preference) {
                        return $preference->label();
                    }, $this->defaultPreferences($variant)),
                    'variant' => $variant,
                ],
                [
                    'action' => $variant ? $this->get('router')->generate('content-alerts-variant', ['variant' => $variant]) : $this->get('router')->generate('content-alerts'),
                ]
            );

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form, &$arguments) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form, &$arguments) {
                    // Check if user not found, opted out or not member of relevant groups.
                    return (!$check instanceof Subscription || $check->getOptout() || empty($check->getPreferences())) ?
                        // Subscribe if true.
                        $this->get('elife.api_client.client.crm_api')
                            ->subscribe(
                                $check instanceof Subscription ? $check->getId() : $form->get('email')->getData(),
                                Subscription::getNewsletters($form->get('preferences')->getData()),
                                $this->prepareSubscriptionNewsletters(),
                                $this->generatePreferencesUrl(),
                                $this->generateUnsubscribeUrl(),
                                $this->generateOptoutUrl(),
                                null,
                                null,
                                $check instanceof Subscription ? $check->getPreferences() : null
                            )
                            ->then(function () use ($form, &$arguments) {
                                $arguments['title'] = 'Thank you for subscribing!';
                                return [
                                    new Paragraph("A confirmation email has been sent to <strong>{$form->get('email')->getData()}</strong>."),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home')),
                                ];
                            }) :
                        // Send preferences link if false.
                        $this->get('elife.api_client.client.crm_api')
                            ->triggerPreferencesEmail($check->getId(), empty($check->getPreferencesUrl()) ? $this->generatePreferencesUrl() : null)
                            ->then(function () use ($form, &$arguments) {
                                $arguments['title'] = 'You are already subscribed';
                                return [
                                    new Paragraph("An email has been sent to <strong>{$form->get('email')->getData()}</strong>."),
                                    new Paragraph('Please follow the link in your email to update your preferences.'),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home')),
                                ];
                            });
                })->wait();
        }, false, true);

        $arguments['contentHeader'] = new ContentHeaderSimple($arguments['title']);

        $arguments['form'] = $validSubmission ?
            $validSubmission :
            $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function updateAction(Request $request, string $id) : Response
    {
        $arguments = $this->simplePageArguments($request);

        $arguments['title'] = 'Your email preferences';

        $data = $this->get('elife.api_client.client.crm_api')
            ->checkSubscription($this->generatePreferencesUrl($id), false)
            ->then(function ($check) {
                if (!$check instanceof Subscription || $check->getOptout()) {
                    throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('content-alerts-link-expired')));
                }

                return $check;
            })
            ->wait();

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, $data instanceof Subscription ? $data->data() : null, ['action' => $this->get('router')->generate('content-alerts-update', ['id' => $id])]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form, &$arguments) {
            return $this->get('elife.api_client.client.crm_api')
                ->subscribe(
                    $form->get('contact_id')->getData(),
                    Subscription::getNewsletters($form->get('preferences')->getData()),
                    [],
                    $this->generatePreferencesUrl(),
                    null,
                    null,
                    $form->get('first_name')->getData(),
                    $form->get('last_name')->getData(),
                    $form->get('groups')->getData() ? Subscription::getNewsletters(explode(',', $form->get('groups')->getData())) : []
                )
                ->then(function () use ($form, &$arguments) {
                    $arguments['title'] = 'Thank you';
                    return [
                        new Paragraph("Email preferences for <strong>{$form->get('email')->getData()}</strong> have been updated."),
                        Button::link('Back to Homepage', $this->get('router')->generate('home')),
                    ];
                })->wait();
        }, false, true);

        $arguments['contentHeader'] = new ContentHeaderSimple($arguments['title']);

        if ($validSubmission) {
            $arguments['form'] = $validSubmission;
        } else {
            $arguments['formIntro'] = new Paragraph("Change which email alerts you receive from eLife. Emails will be sent to <strong>{$form->get('email')->getData()}</strong>.");
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        }

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    public function linkExpiredAction(Request $request) : Response
    {
        $arguments = $this->simplePageArguments($request);

        $arguments['title'] = 'Your email preferences';

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsUpdateRequestType::class, null, ['action' => $this->get('router')->generate('content-alerts-link-expired')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form, &$arguments) {
            return $this->get('elife.api_client.client.crm_api')
                ->checkSubscription($form->get('email')->getData())
                ->then(function ($check) use ($form, &$arguments) {
                    if ($check instanceof Subscription && !$check->getOptout()) {
                        return $this->get('elife.api_client.client.crm_api')
                            ->triggerPreferencesEmail($check->getId(), empty($check->getPreferencesUrl()) ? $this->generatePreferencesUrl() : null)
                            ->then(function () use ($form, &$arguments) {
                                $arguments['title'] = 'Thank you';
                                return [
                                    new Paragraph("An email has been sent to <strong>{$form->get('email')->getData()}</strong>. Please follow the link in the email to update your preferences."),
                                    Button::link('Back to Homepage', $this->get('router')->generate('home')),
                                ];
                            });
                    } else {
                        $arguments['title'] = 'Something went wrong';
                        return [
                            new Paragraph("<strong>{$form->get('email')->getData()}</strong> is not subscribed to email alerts. Please try entering your email address again if you made an error."),
                            new ButtonCollection([
                                Button::link('Try again', $this->get('router')->generate('content-alerts-link-expired')),
                                Button::link('Back to Homepage', $this->get('router')->generate('home'), Button::SIZE_MEDIUM, Button::STYLE_OUTLINE),
                            ]),
                        ];
                    }
                })->wait();
        }, false, true);

        if ($validSubmission) {
            $arguments['form'] = $validSubmission;
        } else {
            $arguments['title'] = 'Your link has expired';
            $arguments['formIntro'] = new Paragraph('Please provide your email address and we will send you an email with a link to update your preferences.');
            $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());
        }

        $arguments['contentHeader'] = new ContentHeaderSimple($arguments['title']);

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }

    /**
     * @param string|null $variant
     * @return Newsletter[]
     */
    private function defaultPreferences(string $variant = null) : array
    {
        switch ($variant) {
            case 'early-career':
                return [new EarlyCareer()];
            case 'technology':
                return [new Technology()];
            case 'elife-newsletter':
                return [new ElifeNewsletter()];
            default:
                return [new LatestArticles()];
        }
    }

    private function generatePreferencesUrl(string $id = null) : string
    {
        return $this->get('router')->generate('content-alerts-update', ['id' => $id ?? uniqid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateUnsubscribeUrl(string $id = null, string $variant = null) : string
    {
        return $this->get('router')->generate('content-alerts-unsubscribe'.($variant ? '-variant' : ''), ['id' => $id ?? uniqid(), 'variant' => $variant], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateOptoutUrl(string $id = null) : string
    {
        return $this->get('router')->generate('content-alerts-optout', ['id' => $id ?? uniqid()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return Newsletter[]
     */
    private function prepareSubscriptionNewsletters() : array
    {
        return [
            new LatestArticles(),
            new EarlyCareer(),
            new Technology(),
            new ElifeNewsletter(),
        ];
    }
}
