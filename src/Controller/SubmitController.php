<?php

namespace eLife\Journal\Controller;

use GuzzleHttp\Psr7\Uri;
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

        // if a return url is specified, check that its from a trusted host
        $returnUrl = $request->query->get('return_url', null);

        if (is_null($returnUrl)) {
            $returnUrl = $this->getParameter('submit_url');
        } else {
            $trustedHosts = $this->getParameter('trusted_hosts');
            $trusted = false;
            
            $uri = new Uri($returnUrl);
            foreach ($trustedHosts as $trustedHost) {
                if (preg_match("/{$trustedHost}/", $uri->getHost())) {
                    $trusted = true;
                }
            }

            if (!$trusted) {
                throw new NotFoundHttpException('Not allowed to see xPub');
            } 
        }

        $redirectUrl = "{$returnUrl}#{$jwt}";

        // return in query arg if specified
        $tokenInQueryArg = $request->query->get('token_in_query', false);

        if ($tokenInQueryArg) {
            $redirectUrl = Uri::withQueryValue(new Uri($returnUrl), 'token', $jwt);
        }

        return new RedirectResponse($redirectUrl);
    }
}
