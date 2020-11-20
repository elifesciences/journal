<?php

namespace eLife\Journal\Controller;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

final class SubmitController extends Controller
{
    public function redirectAction(Request $request) : Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // check that return url is from a trusted host
        $allowedRedirects = $this->getParameter('submit_url_redirects');
        $isAllowed = false;
        $returnUri = new Uri($request->query->get('return_url'));

        foreach ($allowedRedirects as $allowed) {
            if (preg_match('/'.$allowed.'/', $returnUri->getHost())) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            throw new BadRequestHttpException();
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

        return new RedirectResponse(Uri::withQueryValue(
            $returnUri,
            'token',
            $this->get('elife.journal.security.submission.token_generator')->generate($user, $request->getSession()->remove('journal.submit') ?? false)
        ));
    }
}
