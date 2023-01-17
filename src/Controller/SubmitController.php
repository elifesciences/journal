<?php

namespace eLife\Journal\Controller;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

final class SubmitController extends Controller
{
    public function redirectAction(Request $request) : Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // if a return url is specified, check that its from a trusted host
        $returnUrl = $request->query->get('return_url', null);

        if (is_null($returnUrl)) {
            // remove this case once libero reviewer is live and xpub retired
            $returnUrl = $this->getParameter('submit_url');
        } else {
            $allowedRedirects = $this->getParameter('submit_url_redirects');
            $isAllowed = false;
            $uri = new Uri($returnUrl);

            foreach ($allowedRedirects as $allowed) {
                if (preg_match('/'.$allowed.'/', $uri->getHost())) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                throw new BadRequestHttpException();
            }
        }

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

        // remove this case once libero reviewer is live and xpub retired, only return token in query afterwards
        $redirectUrl = "{$returnUrl}#{$jwt}";

        // return in query arg if specified
        $tokenInQueryArg = $request->query->get('token_in_query', false);

        if ($tokenInQueryArg) {
            $redirectUrl = Uri::withQueryValue(new Uri($returnUrl), 'token', $jwt);
        }

        return new RedirectResponse($redirectUrl);
    }
}
