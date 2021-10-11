<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\InfoBar;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentAlertsController extends Controller
{
    public function subscribeAction(Request $request) : Response
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
