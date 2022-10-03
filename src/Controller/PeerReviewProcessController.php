<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PeerReviewProcessController extends Controller {

  public function peerReviewProcessAction(Request $request): Response {
    if (!$this->isGranted('FEATURE_PRC_COMMS')) {
      throw new NotFoundHttpException('Not allowed to see PRC comms');
    }

    $arguments = $this->defaultPageArguments($request);

    $arguments['title'] = 'Peer review process';

    return new Response($this->get('templating')->render('::peer-review-process.html.twig', $arguments));
  }
}
