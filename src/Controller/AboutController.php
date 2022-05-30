<?php

namespace eLife\Journal\Controller;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Exception\EarlyResponse;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel\AboutProfile;
use eLife\Patterns\ViewModel\AboutProfiles;
use eLife\Patterns\ViewModel\ArticleSection;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\FlexibleViewModel;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
use eLife\Patterns\ViewModel\Listing;
use eLife\Patterns\ViewModel\Paragraph;
use eLife\Patterns\ViewModel\SectionListing;
use eLife\Patterns\ViewModel\SectionListingLink;
use eLife\Patterns\ViewModel\SeeMoreLink;
use eLife\Patterns\ViewModel\Select;
use eLife\Patterns\ViewModel\SelectNav;
use eLife\Patterns\ViewModel\SelectOption;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class AboutController extends Controller
{
    const FOUNDING_EDITOR_IN_CHIEF_ID = '6d42f4fe';

    public function aboutAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'About';

        $arguments['contentHeader'] = new ContentHeader('eLife: evolution of publishing', null,
            'Independent, not-for-profit and supported by funders, eLife improves the way research is practised and shared.');

        $arguments['body'] = [
            new Paragraph('eLife is committed to creating a future where a diverse, global community of researchers shares open results for the benefit of all.'),
            new Paragraph('From the research we publish, to the tools we build, to the people we work with, we’ve earned a reputation for quality, integrity and the flexibility to bring about real change.'),
            new Paragraph('We\'re committed to a "<a href="'.$this->get('router')->generate('article', ['id' => '64910']).'">publish, review, curate</a>" model for publishing and plan to achieve this transition by:'),
            Listing::unordered([
                'Peer reviewing preprints in the life sciences and medicine',
                'Building <a href="'.$this->get('router')->generate('about-technology').'">technology</a> to support this model that is open-source, readily adaptable and addresses community needs',
                'Working with scientists from around the world to improve research culture',
            ], 'bullet'),
            new Paragraph('eLife is a non-profit organisation that receives financial support and strategic guidance from the <a href="https://www.hhmi.org/">Howard Hughes Medical Institute</a>, the <a href="https://kaw.wallenberg.org/en">Knut and Alice Wallenberg Foundation</a>, the <a href="https://www.mpg.de/en">Max Planck Society</a> and <a href="https://wellcome.ac.uk/">Wellcome</a>. eLife Sciences Publications Ltd is publisher of the open-access eLife journal (ISSN 2050-084X).'),
            ArticleSection::basic(
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('inside-elife').'">Inside eLife</a>',
                    '<a href="'.$this->get('router')->generate('annual-reports').'">Annual reports</a>',
                    '<a href="'.$this->get('router')->generate('press-packs').'">For the press</a>',
                    '<a href="'.$this->get('router')->generate('resources').'">Resources to download</a>',
                ], 'bullet')), 'Related links', 2
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function aimsScopeAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Aims and scope';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, Research Advances, Scientific Correspondence and Review Articles in the subject areas below.');

        $subjects = $this->get('elife.api_sdk.subjects')->reverse()->slice(0, 100);

        $arguments['body'] = (new PromiseSequence($subjects))
            ->map(function (Subject $subject) {
                $body = $subject->getAimsAndScope()->map($this->willConvertTo(null, ['level' => 2]));

                $editorsLink = $this->render(new SeeMoreLink(
                    new Link('See editors', $this->get('router')->generate('about-people', ['type' => $subject->getId()])),
                    true
                ));

                $lastItem = $body[$i = count($body) - 1];
                if ($body[$i = count($body) - 1] instanceof Paragraph) {
                    $body = $body->set($i, FlexibleViewModel::fromViewModel($lastItem)
                        ->withProperty('text', "{$lastItem['text']} $editorsLink"));
                } else {
                    $body = $body->append(new Paragraph($editorsLink));
                }

                return ArticleSection::basic(
                    $this->render(...$body),
                    $subject->getName(),
                    2,
                    $subject->getId()
                );
            })
            ->then(function (Sequence $sections) {
                return $sections->prepend(
                    new Paragraph('eLife is an open-access journal and complies with all major funding agency requirements for immediate online access to the published results of their research grants.'),
                    new Paragraph('For further details, and requirements for each type of submission, please consult our <a href="https://reviewer.elifesciences.org/author-guide/types">Author Guide.</a>')
                );
            });

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function publishingWithElifeAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Publishing with eLife';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, Research Advances, Scientific Correspondence and Review Articles in the subject areas below.');

        $subjects = $this->get('elife.api_sdk.subjects')->reverse()->slice(0, 100);

        $arguments['body'] = (new PromiseSequence($subjects))
            ->map(function (Subject $subject) {
                $body = $subject->getAimsAndScope()->map($this->willConvertTo(null, ['level' => 2]));

                $editorsLink = $this->render(new SeeMoreLink(
                    new Link('See editors', $this->get('router')->generate('about-people', ['type' => $subject->getId()])),
                    true
                ));

                $lastItem = $body[$i = count($body) - 1];
                if ($body[$i = count($body) - 1] instanceof Paragraph) {
                    $body = $body->set($i, FlexibleViewModel::fromViewModel($lastItem)
                        ->withProperty('text', "{$lastItem['text']} $editorsLink"));
                } else {
                    $body = $body->append(new Paragraph($editorsLink));
                }

                return ArticleSection::basic(
                    $this->render(...$body),
                    $subject->getName(),
                    3,
                    $subject->getId()
                );
            })
            ->then(function (Sequence $aimsAndScope) {
                return [
                    new Paragraph('eLife is an open-access journal, publishing high-quality research in all areas of the life sciences and medicine. It complies with all major funding agency requirements for immediate online access to the published results of their research grants.'),
                    new Paragraph('For further details, and requirements for each type of submission, please consult our <a href="https://reviewer.elifesciences.org/author-guide/types">Author Guide.</a>'),
                    ArticleSection::basic(
                        $this->render(
                            ...$aimsAndScope->prepend(
                                new Paragraph('We welcome the submission of research in the following areas:')
                            )
                        ),
                        'Aims and Scope',
                        2
                    ),
                ];
            });

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peerReviewAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Peer review';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife\'s review process combines the immediacy and openness of preprints with the scrutiny of peer review by experts.');

        $arguments['body'] = [
            new Paragraph('eLife works to improve the process of peer review so that it more effectively conveys the assessment of expert reviewers to authors, readers and other interested parties. We only peer review preprints, and are creating a system in which the outputs of peer review are the primary way research is assessed, rather than journal title. This approach brings together the immediacy and openness of a preprint with the scrutiny offered by peer review.'),
            new Paragraph('eLife\'s editorial process produces two outputs:'),
            Listing::ordered([
                'Public reviews that describe the strengths and weaknesses of the work, and indicate whether the claims and conclusions are justified by the data. An evaluation summary, that captures the major conclusions of the review process, and each of the public reviews are posted alongside the preprint for the benefit of readers, potential readers, and others interested in the work.',
                'Recommendations for the authors, including requests for revisions and suggestions for improvement. The recommendations for the authors are designed to help them revise and improve their preprint. When revised preprints are accepted for publication by eLife, the recommendations for the authors and author responses are published alongside the paper.',
            ], 'roman-lower'),
            new Paragraph('The main features of eLife’s consultative peer-review process are:'),
            Listing::unordered([
                'we only review research papers that have been made available as preprints',
                'our editorial process produces two outputs: i) public reviews on the strengths and weaknesses of the work; ii) recommendations for the authors',
                'all decisions are made by editors who are active researchers',
                'editors and reviewers discuss their reviews with each other; extra experiments, analyses, or data collection are only requested if they are essential and can be reasonably completed within about two months',
                'manuscripts published by eLife include the recommendations to the authors and the author responses',
                'we do not artificially limit the number of articles we publish or have a set acceptance rate',
            ], 'bullet'),
            ArticleSection::basic(
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '00f2f185']).'">Preprints and peer review at eLife</a>',
                    '<a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'e5f8f1f7']).'">What we have learned about preprints</a>',
                    '<a href="'.$this->get('router')->generate('article', ['id' => '64910']).'">Peer Review: eLife implementing "Publish, then Review" model of publishing</a>',
                ], 'bullet')), 'Related links', 2
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function technologyAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Technology';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            ' eLife develops and invests in technology that enhances the sharing and use of research results online.');

        $arguments['body'] = [
            new Paragraph('eLife invests in the development of platforms that support the display, review, and organisation, dissemination and curation of content. '),
            new Paragraph('From considering how to publish preprint content in an enhanced form to developing a system that supports an end-to-end workflow for reviewing preprints, our technology efforts are fully aligned with eLife’s goal to transform research communication by transitioning to a publish, review, curate model of publishing.'),
            new Paragraph('An important step towards this goal is the development of <a href="https://sciety.org/">Sciety</a>, an online application for public preprint evaluation. Built by the team at eLife, Sciety brings together the latest biomedical and life science preprints that are transparently evaluated and curated by communities of experts in one convenient place.'),
            new Paragraph('All software developed at eLife is open source under the most permissible of licences and can be found in our GitHub organisations for <a href="https://github.com/elifesciences">eLife GitHib</a> and <a href="https://github.com/sciety">Sciety GitHub</a>.'),
            ArticleSection::basic(
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'daf1b699']).'">eLife Latest: Announcing a new technology direction</a>',
                    '<a href="https://sciety.org">Sciety.org</a>',
                ], 'bullet')), 'Related links', 2
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function researchCultureAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Research culture';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife recognises that reforming research communication depends on improving research culture.');

        $arguments['body'] = [
            new Paragraph('eLife has an ambitious agenda to reform how research is communicated and assessed, and works to promote a research culture that centres on openness, integrity, and equity, diversity and inclusion. Supported by our <a href="'.$this->get('router')->generate('press-pack', ['id' => '99a91a3b']).'">Communities team</a>, we engage closely with researchers across biology and medicine to drive this change. Updates on many of these activities can be found on our <a href="'.$this->get('router')->generate('community').'">Community page</a>.'),
            new Paragraph('In parallel, eLife publishes <a href="'.$this->get('router')->generate('collection', ['id' => 'edf1261b']).'">articles on research culture</a> and <a href="'.$this->get('router')->generate('collection', ['id' => '3a6a7db3']).'">equity, diversity and inclusion</a>, and provides a platform for the research community to discuss relevant issues through <a href="'.$this->get('router')->generate('collection', ['id' => '1926c529']).'">personal stories</a>, <a href="'.$this->get('router')->generate('interviews').'">interviews</a>, <a href="'.$this->get('router')->generate('podcast').'">podcasts</a> and <a href="'.$this->get('router')->generate('collection', ['id' => '842f35d5']).'">webinars.</a>'),
            new Paragraph('eLife is deeply committed to helping make research and publishing more equitable and inclusive, and we regularly <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '721407a3']).'">report on our actions in these areas</a>.'),
            new Paragraph('With the guidance of our <a href="'.$this->get('router')->generate('about-people', ['type' => 'early-career']).'">Early-Career Advisory Group</a>, eLife has created peer networks through the <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'f744fae0']).'">eLife Community Ambassadors program</a>, <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'c14838ac']).'">supported researchers</a> from underrepresented backgrounds and countries with limited funding, and <a href="https://reviewer.elifesciences.org/reviewer-guide/review-process#involvement-early-career-researchers-peer-review">increased the involvement of early-career researchers in peer review</a>.'),
            new Paragraph('eLife was a founder ­– and continues to be a supporter – of the <a href="https://sfdora.org/">San Francisco Declaration on Research Assessment (DORA)</a>.'),
            ArticleSection::basic(
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('community').'">Community page</a>',
                    '<a href="https://ecrlife.org/">ecrLife</a>',
                    'Follow <a href="https://twitter.com/elifecommunity">eLife Community on Twitter</a>',
                    'Sign up for our <a href="'.$this->get('router')->generate('content-alerts-variant', ['variant' => 'early-career']).'">early-career researchers newsletter</a>',
                ], 'bullet')), 'Related links', 2
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peopleAction(Request $request, string $type) : Response
    {
        if ($request->query->has('type')) {
            return new RedirectResponse(
                $this->get('router')->generate('about-people', ['type' => $request->query->get('type')]),
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        $arguments = $this->aboutPageArguments($request);

        $subjects = $this->get('elife.api_sdk.subjects')->reverse();

        $allSubjects = $subjects->slice(0, 100)
            ->otherwise($this->softFailure('Failed to load subjects for people', new EmptySequence()));

        $types = (new PromiseSequence($allSubjects))
            ->map(function (Subject $subject) use ($type) {
                return new SelectOption($subject->getId(), $subject->getName(), $subject->getId() === $type);
            });

        $types = $types
            ->prepend(new SelectOption('', 'Leadership team', '' === $type))
            ->append(new SelectOption('directors', 'Board of directors', 'directors' === $type))
            ->append(new SelectOption('early-career', 'Early-career advisory group', 'early-career' === $type))
            ->append(new SelectOption('ethics-committee', 'Ethics committee', 'ethics-committee' === $type))
            ->append(new SelectOption('staff', 'Executive staff', 'staff' === $type));

        $people = $this->get('elife.api_sdk.people')->reverse();

        $arguments['lists'] = [];

        switch ($type) {
            case '':
                $arguments['title'] = 'Leadership team';

                $leadership = $people->forType('leadership');

                $editorInChief = $leadership->filter(function (Person $person) {
                    return 'Editor-in-Chief' === $person->getTypeLabel();
                });
                $foundingEditorInChief = $people->get(self::FOUNDING_EDITOR_IN_CHIEF_ID)
                    ->then(function (Person $person) {
                        return new ArraySequence([$person]);
                    })
                    ->otherwise($this->softFailure('Failed to load the Founding Editor-in-Chief', new EmptySequence()));
                $deputyEditors = $leadership->filter(function (Person $person) {
                    return 'Editor-in-Chief' !== $person->getTypeLabel();
                });

                $arguments['lists'][] = $this->createAboutProfiles($editorInChief, 'Editor-in-Chief');
                $arguments['lists'][] = $this->createAboutProfiles($deputyEditors, 'Deputy editors');
                $arguments['lists'][] = $this->createAboutProfiles($people->forType('senior-editor'), 'Senior editors');
                $arguments['lists'][] = $this->createAboutProfiles($foundingEditorInChief->wait(), 'Founding Editor-in-Chief');
                break;
            case 'directors':
                $arguments['title'] = 'Board of directors';

                $arguments['lists'][] = $this->createAboutProfiles($people->forType('director'), 'Board of directors');
                break;
            case 'early-career':
                $arguments['title'] = 'Early-career advisory group';

                $arguments['lists'][] = $this->createAboutProfiles($people->forType('early-career'), 'Early-career advisory group');
                break;
            case 'ethics-committee':
                $arguments['title'] = 'Ethics committee';

                $allEthicsCommittee = $people->forType('ethics-committee');

                $chair = $allEthicsCommittee->filter(function (Person $person) {
                    return 'Chair' === $person->getTypeLabel();
                });

                $ethicsCommittee = $allEthicsCommittee->filter(function (Person $person) {
                    return 'Chair' !== $person->getTypeLabel();
                });
                $arguments['lists'][] = $this->createAboutProfiles($chair, 'Chair');
                $arguments['lists'][] = $this->createAboutProfiles($ethicsCommittee, 'Ethics committee');

                $impactStatement = 'A new eLife Ethics Committee will advise and develop policy focused on establishing and maintaining the highest standards of research and publication practices across the scope of the journal.';
                break;
            case 'staff':
                $arguments['title'] = 'Executive staff';

                $arguments['lists'][] = $this->createAboutProfiles($people->forType('executive'), 'Executive staff');
                break;
            default:
                $arguments['subject'] = $subjects->get($type)->otherwise($this->mightNotExist())
                    ->then(function (Subject $subject) use ($type) {
                        if ($subject->getId() !== $type) {
                            throw new EarlyResponse(new RedirectResponse($this->get('router')->generate('about-people', ['type' => $subject->getId()])));
                        }

                        return $subject;
                    });

                $arguments['title'] = $arguments['subject']->then(function (Subject $subject) {
                    return "Editors for {$subject->getName()}";
                });

                $people = $people->forSubject($type);
                $arguments['lists'][] = $this->createAboutProfiles($people->forType('leadership', 'senior-editor'), 'Senior editors');
                $arguments['lists'][] = $this->createAboutProfiles($people->forType('reviewing-editor'), 'Reviewing editors', true);

                $impactStatement = $arguments['subject']->then(function (Subject $subject) {
                    if ($subject->getAimsAndScope()->notEmpty()) {
                        return $subject->getAimsAndScope()[0]->getText();
                    }

                    return null;
                });
        }

        $arguments['contentHeader'] = all(['types' => $types, 'title' => promise_for($arguments['title']), 'impactStatement' => promise_for($impactStatement ?? null)])
            ->then(function (array $parts) {
                $impactStatement = $parts['impactStatement'] ?? 'eLife’s editors, early-career advisors, governing board, and executive staff work in concert to realise our mission.';

                return new ContentHeader($parts['title'], null, $impactStatement,
                    false, [], null, null, null, null,
                    new SelectNav(
                        $this->get('router')->generate('about-people'),
                        new Select('type', $parts['types']->toArray(), new FormLabel('Type', true), 'type'),
                        Button::form('Go', Button::TYPE_SUBMIT, 'go', Button::SIZE_EXTRA_SMALL)
                    )
                );
            });

        $arguments['lists'] = array_filter($arguments['lists'], Callback::isNotEmpty());

        return new Response($this->get('templating')->render('::about-people.html.twig', $arguments));
    }

    private function createAboutProfiles(Sequence $people, string $heading, bool $compact = false)
    {
        if ($people->isEmpty()) {
            return null;
        }

        return new AboutProfiles($people->map($this->willConvertTo(AboutProfile::class, compact('compact')))->toArray(), new ListHeading($heading), $compact);
    }

    private function aboutPageArguments(Request $request) : array
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments['menuLink'] = new SectionListingLink('All sections', 'sections');

        $menuItems = [
            'About eLife' => $this->get('router')->generate('about'),
            'Publishing with eLife' => $this->get('router')->generate('about-publishing-with-elife'),
            'Editors and people' => $this->get('router')->generate('about-people'),
            'Peer review' => $this->get('router')->generate('about-peer-review'),
            'Technology' => $this->get('router')->generate('about-technology'),
            'Research Culture' => $this->get('router')->generate('about-research-culture'),
        ];

        $currentPath = $this->get('router')->generate($request->attributes->get('_route'), $request->attributes->get('_route_params'));

        $menuItems = array_map(function (string $text, string $path) use ($currentPath) {
            return new Link($text, $path, $path === $currentPath);
        }, array_keys($menuItems), array_values($menuItems));

        $arguments['menu'] = new SectionListing('sections', $menuItems, new ListHeading('About sections'), true);

        return $arguments;
    }
}
