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

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null, 'eLife\'s media policy is designed to encourage high-quality, informed and widespread discussion of research that we peer-review and publish.');

        $arguments['body'] = [
            ArticleSection::basic($this->render(
                new Paragraph('eLife only peer-reviews and publishes articles that are made publicly available as preprints. We recently launched <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '741dbe4d', 'slug' => 'elife-s-new-model-open-for-submissions']).'">a new publishing model</a> that ends the accept/reject decision after peer review. Instead, papers invited for review are published as a Reviewed Preprint that contains public peer reviews and <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'db24dd46', 'slug' => 'elife-s-new-model-what-is-an-elife-assessment']).'">an eLife assessment</a>. We also continue to publish research that was accepted after peer review as part of our traditional process.'),
                new Paragraph('As articles are already available as preprints, authors are free to present their findings to their peers, including at meetings and conferences, and to blog about their work. They are also welcome to speak to the media about their study at any time and may share their preprint with journalists as they prefer. Depending on which of our processes they submit their work to, they may also wish to ask their institutional press officers to help with advance promotion of their eLife journal article or Reviewed Preprint. However, eLife encourages press officers to pitch studies widely at the time of publication only, rather than in advance, so that as many journalists as possible receive the story, and access to the peer-reviewed eLife paper or Reviewed Preprint, at the same time.')
            ), 'Media outreach prior to publication', 2),
            ArticleSection::basic($this->render(
                new Paragraph('Because eLife only peer-reviews articles that are already available as preprints, we do not release our content under embargo. This means that journalists can write and publish articles about an eLife paper or Reviewed Preprint at any time without breaking an embargo. However, we strongly recommend that their stories are published at the time of or after publication, so that readers have access to the peer-reviewed paper. As a result of our policy not to embargo, both eLife and press officers from external organisations are not able to submit press releases under embargo to news websites such as EurekAlert! and AlphaGalileo.')
            ), 'Our policy not to embargo eLife papers', 2),
            ArticleSection::basic($this->render(
                new Paragraph('eLife has always supported the rapid and open sharing of new research through publication on preprint servers. As part of our new publishing model, Reviewed Preprints are published alongside the public reviews and an eLife assessment that reflects the significance of the findings and the strength of the evidence reported in the preprint. Author responses are also included where available. In our traditional model, many eLife papers are published with a plain-language summary (called an eLife digest) to explain the background and central findings of the work to a broad readership. We also publish the most substantive parts of the decision letter that is sent to authors after peer review (and which is based on the referees\' reports on the paper), along with the authors\' response to this letter, to provide greater context for the work. Where eLife considers papers to be of potential interest to a broad audience, we will also promote these widely to the media and to interested readers either on the day of publication or post publication.')
            ), 'Making research content widely accessible', 2),
            ArticleSection::basic($this->render(
                new Paragraph('In addition to research content published in eLife, we distribute press releases relating to developments within our organisation. As this is non-research content, we may issue it in advance with an embargo if we feel it necessary to do so.')
            ), 'Corporate news and announcements', 2),
        ];

        return new Response($this->get('templating')->render('::media-policy.html.twig', $arguments));
    }
}
