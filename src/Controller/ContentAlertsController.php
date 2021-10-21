<?php

namespace eLife\Journal\Controller;

use eLife\Journal\Form\Type\ContentAlertsType;
use eLife\Journal\Guzzle\CiviCrmClient;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
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
            ->create(ContentAlertsType::class, ['preferences' => [CiviCrmClient::LABEL_LATEST_ARTICLES]], ['action' => $this->get('router')->generate('content-alerts')]);

        $validSubmission = $this->ifFormSubmitted($request, $form, function () use ($form) {
            return $this->get('elife.api_client.client.crm_api')
                ->subscribe(
                    $form->get('email')->getData(),
                    $form->get('preferences')->getData()
                )
                ->then(function () use ($form) {
                    return "A confirmation email has been sent to <strong>{$form->get('email')->getData()}</strong>.";
                })
                ->wait();
        }, false);

        $arguments['form'] = $validSubmission ?
            ArticleSection::basic(
                'Thank you for subscribing!',
                2,
                $this->render(new Paragraph($validSubmission)).$this->render(
                    Button::link('Back to Homepage', $this->get('router')->generate('home'))
                ),
                'thank-you'
            ) :
            $this->get('elife.journal.view_model.converter')->convert($form->createView());

        return new Response($this->get('templating')->render('::content-alerts.html.twig', $arguments));
    }
}
