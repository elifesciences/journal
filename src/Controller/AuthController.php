<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\OrcidVisibilitySettingError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class AuthController extends Controller
{
    use TargetPathTrait;

    public function redirectAction(Request $request) : Response
    {
        if ($referer = trim($request->headers->get('Referer'))) {
            $this->saveTargetPath($request->getSession(), 'main', $referer);
        }

        return $this->get('oauth2.registry')
            ->getClient('elife')
            ->redirect();
    }

    public function orcidVisibilitySettingAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request) + [
            'title' => 'Error',
            'error' => new OrcidVisibilitySettingError($this->get('router')->generate('contact')),
        ];

        return new Response($this->get('templating')->render('::exception.html.twig', $arguments));
    }
}
