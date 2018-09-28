<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SubmitController extends Controller
{
    public function redirectAction(Request $request) : Response
    {
        if (!$this->isGranted('FEATURE_XPUB')) {
            throw new NotFoundHttpException('Not allowed to see xPub');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->get('router')->generate('log-in'));
        }

        $jwt = $this->get('elife.journal.security.xpub.token_generator')->generate($user);

        return new RedirectResponse("{$this->getParameter('submit_url')}#{$jwt}");
    }
}
