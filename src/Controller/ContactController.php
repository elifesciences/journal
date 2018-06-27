<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Form\Type\ContactType;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\InfoBar;
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
                    'Thanks '.$form->get('name')->getData().', we have received your question.');

            $response = implode("\n\n", array_map(function (FormInterface $child) {
                $label = ($child->getConfig()->getOption('label') ?? Humanizer::humanize($child->getName()));

                return $label."\n".str_repeat('-', strlen($label))."\n".$child->getData();
            }, array_filter(iterator_to_array($form), function (FormInterface $child) {
                return !in_array($child->getConfig()->getType()->getBlockPrefix(), ['submit']);
            })));

            $message1 = (new Swift_Message())
                ->setSubject('Question to eLife')
                ->setFrom('do_not_reply@elifesciences.org')
                ->setTo($form->get('email')->getData(), $form->get('name')->getData())
                ->setBody('Thanks for your question. We will respond as soon as we can.

eLife Sciences Publications, Ltd is a limited liability non-profit non-stock corporation incorporated in the State of Delaware, USA, with company number 5030732, and is registered in the UK with company number FC030576 and branch number BR015634 at the address First Floor, 24 Hills Road, Cambridge CB2 1JP.');

            $message2 = (new Swift_Message())
                ->setSubject('Question submitted: '.$form->get('subject')->getData())
                ->setFrom('do_not_reply@elifesciences.org')
                ->setTo('staff@elifesciences.org')
                ->setBody('A question has been submitted on '.$this->get('router')->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL).'

'.$response);

            $this->get('mailer')->send($message1);
            $this->get('mailer')->send($message2);
        });

        $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::contact.html.twig', $arguments));
    }
}
