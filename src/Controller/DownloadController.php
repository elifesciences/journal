<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

final class DownloadController extends Controller
{
    public function fileAction(Request $request) : Response
    {
        try {
            $link = $this->get('elife.journal.helper.download_link_uri_generator')->check($request->getUri());
        } catch (UnexpectedValueException $e) {
            throw new NotFoundHttpException('Not a valid signed URI', $e);
        }

        $response = $this->get('elife.journal.helper.http_proxy')->send($request, $link->getUri());

        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $link->getFilename()));

        if ($link->getCanonicalUri()) {
            $response->headers->set('Link', sprintf('<%s>; rel="canonical"', $link->getCanonicalUri()));
        }

        return $response;
    }
}
