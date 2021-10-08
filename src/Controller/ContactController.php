<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Form\Type\ContactType;
use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Paragraph;
use Swift_Message;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContactController extends Controller
{
    public function contactAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Contact';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContactType::class, null, ['action' => $this->get('router')->generate('contact')]);

        $this->ifFormSubmitted($request, $form, function () use ($form) {
            $this->get('session')
                ->getFlashBag()
                ->add(InfoBar::TYPE_SUCCESS,
                    'Thank you for your question. We will respond as soon as we can.');

            $response = implode("\n\n", array_map(function (FormInterface $child) {
                $label = ($child->getConfig()->getOption('label') ?? Humanizer::humanize($child->getName()));

                return $label."\n".str_repeat('-', strlen($label))."\n".$child->getData();
            }, array_filter(iterator_to_array($form), function (FormInterface $child) {
                return !in_array($child->getConfig()->getType()->getBlockPrefix(), ['submit']);
            })));

            switch ($form->get('subject')->getData()) {
                case 'Author query':
                    $emailAddress = 'editorial@elifesciences.org';
                    break;
                case 'Press query':
                    $emailAddress = 'press@elifesciences.org';
                    break;
                default:
                    $emailAddress = 'site-feedback@elifesciences.org';
            }

            $message = (new Swift_Message())
                ->setSubject('Question submitted: '.$form->get('subject')->getData())
                ->setFrom('do_not_reply@elifesciences.org')
                ->setTo($emailAddress)
                ->setBody('A question has been submitted on '.$this->get('router')->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL).'

'.$response);

            $this->get('mailer')->send($message);
        });

        $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::contact.html.twig', $arguments));
    }

    public function contentAlertsAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Subscribe to eLife\'s email alerts';

        $arguments['contentHeader'] = new ContentHeader($arguments['title']);

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContentAlertsType::class, null, ['action' => $this->get('router')->generate('content-alerts')]);

        $this->ifFormSubmitted($request, $form, function () use ($form) {
            $this->get('elife.api_client.client.crm_api')
                ->subscribe(
                    $form->get('email')->getData(),
                    $form->get('first_name')->getData(),
                    $form->get('last_name')->getData(),
                    $form->get('preferences')->getData()
                )
                ->wait();

            $this->get('session')
                ->getFlashBag()
                ->add(InfoBar::TYPE_SUCCESS, "A confirmation email has been sent to <strong>{$form->get('email')->getData()}</strong>.");
        });

        $successMessage = $this->get('session')->getFlashBag()->get(InfoBar::TYPE_SUCCESS);

        $arguments['form'] = empty($successMessage) ?
            $this->get('elife.journal.view_model.converter')->convert($form->createView()) :
            ArticleSection::basic(
                'Thank you for subscribing!',
                2,
                $this->render(
                    ...array_map(function ($message) {
                        return new Paragraph($message);
                    }, $successMessage)
                ).$this->render(
                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                )
            );

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }
}
