<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MediaPolicyController extends Controller
{
    public function mediaPolicyAction(Request $request) : Response
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['title'] = 'Media policy';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, 'eLife\'s media policy is designed to encourage high-quality, informed and widespread discussion of new research &mdash; before and after publication.');

        $arguments['body'] = [
            ArticleSection::basic($this->render(
                new Paragraph('Prior to publication, authors are encouraged to present their findings to their peers, including at meetings and conferences; to deposit a copy of their manuscript with a preprint server (or other open repository or website); and to blog about their findings. None of these activities will affect consideration of a manuscript for publication in eLife.'),
                new Paragraph('Authors are also welcome to speak to the media about their work at any time and may share advance copies of their manuscript with journalists as they prefer. They may also wish to ask their institutional press officers to help with advance promotion, once a manuscript is accepted. However, eLife encourages press officers to pitch studies widely at the time of publication only, rather than in advance, so that as many journalists as possible receive the story, and access to the full, peer-reviewed paper, at the same time.')
            ), 'Media outreach prior to publication', 2),
            ArticleSection::basic($this->render(
                new Paragraph('Because authors are completely free to release their content ahead of publication and to talk with the media at any stage, we do not release content under embargo, except under exceptional circumstances. This means that journalists can write and publish articles about a paper in advance of publication without breaking an embargo. However, we strongly recommend that their stories are published at the time of or after publication, so that readers have access to the full, peer-reviewed paper. As a result of our policy not to embargo, both eLife and press officers from external organisations are not able to submit press releases under embargo to news websites such as EurekAlert! and AlphaGalileo.'),
                new Paragraph('There may be exceptional circumstances where eLife considers it necessary to embargo papers. This will be decided on a case-by-case basis. Exceptional circumstances include cases where the findings are of extreme public interest (e.g. because they have significant public health implications); are particularly complex (e.g. because the conclusions are not straightforward and could be misinterpreted); involve multiple parties; and/or are discussed across multiple pieces of eLife content. Where these circumstances apply, eLife can also grant permission for press releases to be submitted to EurekAlert! and other news websites under embargo.')
            ), 'Our policy not to embargo eLife papers', 2),
            ArticleSection::basic($this->render(
                new Paragraph('Many eLife papers are published with a plain-language summary (called an eLife digest) to explain the background and central findings of the work to a broad readership. We also publish the most substantive parts of the decision letter that is sent to authors after peer review (and which is based on the referees\' reports on the paper), along with the authors\' response to this letter, to provide greater context for the work.')
            ), 'Making research content widely accessible', 2),
            ArticleSection::basic($this->render(
                new Paragraph('In addition to research content published in eLife, we distribute press releases relating to developments within our organisation. As this is non-research content, we may issue it in advance with an embargo if we feel it necessary to do so.')
            ), 'Corporate news and announcements', 2),
        ];

        return new Response($this->get('templating')->render('::media-policy.html.twig', $arguments));
    }
}
