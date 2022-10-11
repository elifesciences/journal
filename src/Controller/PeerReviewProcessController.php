<?php

namespace eLife\Journal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eLife\Patterns\ViewModel\ContentHeader;

final class PeerReviewProcessController extends Controller
{
    public function peerReviewProcessAction(Request $request): Response
    {
        if (!$this->isGranted('FEATURE_PRC_COMMS')) {
            throw new NotFoundHttpException('Not allowed to see PRC comms');
        }

        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'eLife’s peer review process';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, 'eLife is changing its editorial process to emphasize public reviews and assessments of preprints by eliminating accept/reject decisions after peer review.<p>To learn more about why eLife’s process is changing, <a href="#" class="peer-review-process-content-header-link">read the Editorial</a>.</p>');

        return new Response($this->get('templating')->render('::peer-review-process.html.twig', $arguments));
    }
}
