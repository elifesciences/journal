<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class AuthController extends Controller
{
    use TargetPathTrait;

    public function redirectAction(Request $request) : Response
    {
        if (!$this->isGranted('FEATURE_CAN_AUTHENTICATE')) {
            throw new NotFoundHttpException('Not found');
        }

        if ($referer = trim($request->headers->get('Referer'))) {
            $firewall = $this->get('security.firewall.map')->getFirewallConfig($request);
            $this->saveTargetPath($request->getSession(), $firewall->getName(), $referer);
        }

        return $this->get('oauth2.registry')
            ->getClient('elife')
            ->redirect();
    }
}
