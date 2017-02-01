<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Form\Type\ContactType;
use eLife\Journal\Helper\Humanizer;
use eLife\Patterns\ViewModel\InfoBar;
use Swift_Message;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContactController extends Controller
{
    public function contactAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments();

        $arguments['title'] = 'Contact';

        /** @var Form $form */
        $form = $this->get('form.factory')
            ->create(ContactType::class, null, ['action' => $this->get('router')->generate('contact')]);

        $form->handleRequest($request);

        if ($form->isValid()) {
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

            $message1 = Swift_Message::newInstance()
                ->setSubject('Question to eLife')
                ->setFrom('do_not_reply@elifesciences.org')
                ->setTo($form->get('email')->getData(), $form->get('name')->getData())
                ->setBody('Thanks for your question. We will respond as soon as we can.');

            $message2 = Swift_Message::newInstance()
                ->setSubject('Question submitted')
                ->setFrom('do_not_reply@elifesciences.org')
                ->setTo('staff@elifesciences.org')
                ->setBody('A question has been submitted on '.$this->get('router')->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL).'

'.$response);

            $this->get('mailer')->send($message1);
            $this->get('mailer')->send($message2);

            return new RedirectResponse($this->get('router')->generate('contact'));
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->get('session')
                    ->getFlashBag()
                    ->add(InfoBar::TYPE_ATTENTION, $error->getMessage());
            }
        }

        $arguments['form'] = $this->get('elife.journal.view_model.converter')->convert($form->createView());

        $response = new Response($this->get('templating')->render('::contact.html.twig', $arguments));
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }
}
