<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

final class SubmitController extends Controller
{
    public function redirectAction(Request $request) : Response
    {
        if (!$this->isGranted('FEATURE_XPUB')) {
            throw new NotFoundHttpException('Not allowed to see xPub');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $path = [
                '_forwarded' => $request->attributes,
                '_controller' => 'AppBundle:Auth:redirect',
            ];
            $subRequest = $request->duplicate(null, null, $path);
            $subRequest->headers->set('Referer', $request->getUri());
            $subRequest->getSession()->set('journal.submit', true);

            return $this->get('kernel')->handle($subRequest, KernelInterface::SUB_REQUEST);
        }

        $jwt = $this->get('elife.journal.security.xpub.token_generator')->generate($user, $request->getSession()->remove('journal.submit') ?? false);

        return new RedirectResponse("{$this->getParameter('submit_url')}#{$jwt}");
    }
}
