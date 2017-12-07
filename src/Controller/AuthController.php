<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class AuthController extends Controller
{
    public function redirectAction() : Response
    {
        if (!$this->isGranted('FEATURE_CAN_AUTHENTICATE')) {
            throw new NotFoundHttpException('Not found');
        }

        $request = $this->get('request_stack')->getCurrentRequest();
        $path['_forwarded'] = $request->attributes;
        $path['_controller'] = 'HWIOAuthBundle:Connect:redirectToService';
        $path['service'] = 'elife';
        $subRequest = $request->duplicate([], null, $path);

        return $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
