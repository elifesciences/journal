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

        return new Response($this->get('templating')->render('::contact.html.twig', $arguments));
    }
}
