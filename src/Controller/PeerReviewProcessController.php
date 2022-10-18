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
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'eLife’s peer review process';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, '<p>eLife is changing its editorial process to emphasize public reviews and assessments of preprints by eliminating accept/reject decisions after peer review.</p><p>To learn more about why eLife’s process is changing, <a href="{{ path(\'article\', {id: \'83889\'}) }}" class="peer-review-process-content-header-link">read the Editorial</a>.</p>');

        return new Response($this->get('templating')->render('::peer-review-process.html.twig', $arguments));
    }
}
