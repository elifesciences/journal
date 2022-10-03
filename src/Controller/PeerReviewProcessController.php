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

    throw new NotFoundHttpException('Nope');
  }
}
