<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PeerReviewProcessController {

  public function peerReviewProcessAction(Request $request) : Response
  {
    throw new NotFoundHttpException('Nope');
  }
}
