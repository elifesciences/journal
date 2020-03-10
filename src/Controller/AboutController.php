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

        $arguments['contentHeader'] = new ContentHeader('eLife: Accelerating discovery', null,
            'eLife is an initiative from research funders to transform research communication through improvements to science publishing, technology and research culture.');

        $arguments['body'] = [
            new Paragraph('eLife is a non-profit organisation created by funders and led by researchers. Our mission is to accelerate discovery by operating a platform for research communication that encourages and recognises the most responsible behaviours.'),
            new Paragraph('We work across three major areas:'),
            Listing::ordered([
                'Publishing – eLife aims to publish work of the highest standards and importance in all areas of biology and medicine, while exploring creative new ways to improve how research is assessed and published.',
                'Technology – eLife invests in open-source technology innovation to modernise the infrastructure for science publishing and improve online tools for sharing, using and interacting with new results.',
                'Research culture – eLife is committed to working with the worldwide research community to promote responsible behaviours in <research class=""></research>)',
            ], 'number'),
            new Paragraph('eLife receives financial support and strategic guidance from the <a href="https://https://www.hhmi.org/">Howard Hughes Medical Institute</a>, the <a href="https://kaw.wallenberg.org/en">Knut and Alice Wallenberg Foundation</a>, the <a href="https://www.mpg.de/en">Max Planck Society</a> and <a href="https://wellcome.ac.uk/">Wellcome</a>. eLife Sciences Publications Ltd is publisher of the open-access eLife journal (ISSN 2050-084X).'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://elifesciences.org/inside-elife">Inside eLife</a>',
                    '<a href="https://elifesciences.org/annual-reports">Annual reports</a>',
                    '<a href="https://elifesciences.org/for-the-press">For the press</a>',
                    '<a href="https://elifesciences.org/resources">Resources to download</a>',
                ], 'bullet'))
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
                    $subject->getName(),
                    2,
                    $this->render(...$body),
                    $subject->getId()
                );
            })
            ->then(function (Sequence $sections) {
                if ($sections->isEmpty()) {
                    $sections = $sections->append(new Paragraph('No subjects available.'));
                }

                return $sections;
            })
            ->then(function (Sequence $sections) {
                return $sections->prepend(
                    new Paragraph('eLife is an open-access journal and complies with all major funding agency requirements for immediate online access to the published results of their research grants.'),
                    new Paragraph('For further details, and requirements for each type of submission, please consult our <a href="https://reviewer.elifesciences.org/author-guide/types">Author Guide.</a>')
                );
            });

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peerReviewAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Peer review';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife aims to improve the speed, visibility and usefulness of peer review.');

        $arguments['body'] = [
            new Paragraph('eLife works to improve the process of peer review so that it more effectively conveys the assessment of expert reviewers to authors, readers and other interested parties. This ambition is reflected in the transparency of our current process, where anyone can read the peer review reports and editors\' assessment for all published eLife papers. In the future we envision a system in which the outputs of peer review are the primary way research is assessed, rather than journal title.'),
            new Paragraph('The main features of the current eLife peer-review process are:'),
            Listing::unordered([
                'all decisions are made by editors who are active researchers in the life and biomedical sciences.',
                'we do not artificially limit the number of articles we publish or have a set acceptance rate.',
                'editors and reviewers discuss their reviews with each other before reaching a decision on a manuscript; extra experiments are only requested if they are essential and can be completed within about two months.',
                'the decision letter sent to the author after peer review, and the authors\' response to this letter, are published with accepted manuscripts.',
            ], 'bullet'),
            new Paragraph('The overall aim is to make peer review faster (by reducing rounds of revision and only requesting extra experiments when they are essential), fairer and more open. <a href="'.$this->get('router')->generate('article', ['id' => '00855']).'">eLife does not support the Impact Factor</a> and is a co-founder of the <a href="https://sfdora.org/">Declaration on Research Assessment (DORA)</a>. Regularly updated metrics relating to the eLife editorial process are available in our <a href="https://reviewer.elifesciences.org/author-guide/journal-metrics">Author Guide</a>.'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://elifesciences.org/inside-elife/e9091cea/peer-review-new-initiatives-to-enhance-the-value-of-elife-s-process">Peer Review: New initiatives to enhance the value of eLife’s process</a> (Michael Eisen, eLife Editor-in-Chief, November 2019)',
                    '<a href="https://reviewer.elifesciences.org/author-guide/editorial-process">Author Guide</a>',
                    '<a href="https://www.youtube.com/watch?v=quCG17jZW-w">eLife: Changing the review process</a> (video, February 2014)',
                ], 'bullet'))
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function technologyAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Technology';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife’s open-source technology initiatives enhance the communication and use of research results online.');

        $arguments['body'] = [
            new Paragraph('eLife invests heavily in software development, experience design, collaboration and outreach to help realise the full potential of the digital communication of new research. We support the development of open-source tools that can be used, adopted and extended by any interested party to help move towards an ecosystem that serves research as efficiently and as cost-effectively as possible.'),
            new Paragraph('In parallel to our in-house software development efforts, the eLife Innovation Initiative is a separately funded effort aimed at providing funding, training and community support for creative individuals and teams within the academic and technology industries. The primary outputs of the Initiative are open solutions aimed at improving the discovery, sharing, consumption and evaluation of research.'),
            new Paragraph('Through this Initiative, we’re always on the lookout for opportunities to engage with the best emerging talent and ideas at the interface of research and technology. You can find out more about some of these engagements on eLife Labs, or contact our Innovation Community Manager for more information (innovation@elifesciences.org).'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://libero.pub/">Libero: eLife’s open-source platform for academic publishing</a>',
                    '<a href="https://elifesciences.org/labs">eLife Labs</a>',
                    '<a href="https://crm.elifesciences.org/crm/tech-news">Sign up for Technology and Innovation news from eLife</a>',
                ], 'bullet'))
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
            new Paragraph('eLife seeks to encourage and recognise responsible behaviours in research, and to promote a research culture that supports collaboration, diversity and inclusion, and openness. We support preprints and open-science practices, and publish articles on different aspects of research culture on our Community page. eLife was a founder of the San Francisco Declaration on Research Assessment (DORA).'),
            new Paragraph('eLife invests in research culture in a variety of ways, many of which involve working closely with early-career researchers (ECRs). With the guidance of our Early-Career Advisory Group, we have established standards for diversity and inclusion across eLife, created peer networks and the eLife Ambassadors program, increased ECR involvement on our editorial board and reviewer pool, awarded grants to early-career authors, and showcased early-career talents and perspectives through interviews, podcasts and webinars.'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://elifesciences.org/community">Community page</a>',
                    '<a href="https://ecrlife.org/">ecrLife</a>',
                    'Sign up for our <a href="https://crm.elifesciences.org/crm/community-news">early-career newsletter</a>',
                ], 'bullet'))
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
                    false, [], null, [], [], null, null,
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
            'Aims and scope' => $this->get('router')->generate('about-aims-scope'),
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
