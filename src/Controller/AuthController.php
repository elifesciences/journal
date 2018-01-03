<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AuthController extends Controller
{
    public function redirectAction() : Response
    {
        if (!$this->isGranted('FEATURE_CAN_AUTHENTICATE')) {
            throw new NotFoundHttpException('Not found');
        }

        return $this->get('oauth2.registry')
            ->getClient('elife')
            ->redirect();
    }
}
