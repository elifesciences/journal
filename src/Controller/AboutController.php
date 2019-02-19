<?php

namespace eLife\Journal\Controller;

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
use eLife\Patterns\ViewModel\DefinitionList;
use eLife\Patterns\ViewModel\FlexibleViewModel;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\IFrame;
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
    public function aboutAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'About';

        $arguments['contentHeader'] = new ContentHeader('About eLife', null,
            'We want to improve all aspects of research communication in support of excellence in science');

        $arguments['body'] = [
            new Paragraph('eLife is a non-profit organisation inspired by research funders and led by scientists. Our mission is to help scientists accelerate discovery by operating a platform for research communication that encourages and recognises the most responsible behaviours in science.'),
            new Paragraph('eLife publishes work of the highest scientific standards and importance in all areas of the life and biomedical sciences. The research is selected and evaluated by working scientists and is made freely available to all readers without delay. eLife also invests in <a href="'.$this->get('router')->generate('about-innovation').'">innovation</a> through open-source tool development to accelerate research communication and discovery. Our work is guided by the <a href="'.$this->get('router')->generate('about-people').'">communities</a> we serve.'),
            new Paragraph('eLife was founded in 2011 by the Howard Hughes Medical Institute, the Max Planck Society and the Wellcome Trust​. These organisations continue to provide financial and strategic support, and were joined by the Knut and Alice Wallenberg Foundation for 2018. <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'b6365b76']).'">Publication fees​</a>​ ​were introduced in 2017 ​to cover some of ​eLife\'s <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => 'a058ec77']).'">core publishing costs</a>. <a href="'.$this->get('router')->generate('annual-reports').'">​Annual reports​</a>​ and financial statements are openly available.'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('inside-elife').'">Inside eLife</a>',
                    '<a href="'.$this->get('router')->generate('annual-reports').'">Annual reports</a>',
                    '<a href="'.$this->get('router')->generate('press-packs').'">For the press</a>',
                    '<a href="'.$this->get('router')->generate('resources').'">Resources to download</a>',
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
            'eLife welcomes the submission of Research Articles, Short Reports, Tools and Resources articles, and Research Advances (read more about <a href="https://submit.elifesciences.org/html/elife_author_instructions.html#types">article types</a>) in the following subject areas.');

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
            });

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function peerReviewAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Peer review';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            '"Our goal at eLife is to publish papers that our reviewers and editors find authoritative, rigorous, insightful, enlightening or just beautiful"*');

        $arguments['body'] = [
            new IFrame('https://www.youtube.com/embed/3pjXfgeOCho', 1280, 720),
            new Paragraph('At eLife, we publish highly influential research, in the form of <a href="'.$this->get('router')->generate('article-type', ['type' => 'research-article']).'">Research Articles</a> (with no limits to the length or number of figures), <a href="'.$this->get('router')->generate('article-type', ['type' => 'short-report']).'">Short Reports</a>, <a href="'.$this->get('router')->generate('article-type', ['type' => 'tools-resources']).'">Tools and Resources</a>, and <a href="'.$this->get('router')->generate('article-type', ['type' => 'research-advance']).'">Research Advances</a> (substantial developments that build on previous eLife papers). eLife is also home to a rich selection of <a href="'.$this->get('router')->generate('magazine').'">Magazine</a> content, including <a href="'.$this->get('router')
                    ->generate('article-type', ['type' => 'editorial']).'">Editorials</a>, essays and opinions (<a href="'.$this->get('router')->generate('article-type', ['type' => 'feature']).'">Feature Articles</a>), expert commentaries on recent papers (<a href="'.$this->get('router')->generate('article-type', ['type' => 'insight']).'">Insights</a>), <a href="'.$this->get('router')->generate('podcast').'">podcasts</a>, and interviews. For details and requirements for each type of submission, please consult our <a href="https://submit.elifesciences.org/html/elife_author_instructions.html">Author Guide and Policies</a>.'),
            new Paragraph('We do not artificially limit the number of articles we publish or have a set acceptance rate. Rather, we rely on the judgment of the working scientists who serve as our <a href="'.$this->get('router')->generate('about-people').'">editors</a> to select papers for peer review and publication. eLife does not support the <a href="https://doi.org/10.7554/elife.00855">Impact Factor</a>.'),
            new Paragraph('Our goal is to make peer review constructive and collaborative: initial decisions are delivered quickly; working scientists make all editorial decisions; and revision requests are consolidated following an open, internal consultation among reviewers to deliver a single, concise set of the essential revisions. Post-review decisions and author responses for published papers are available for all to read.'),
            new Paragraph('Regularly updated metrics relating to the eLife editorial process are available in our <a href="https://submit.elifesciences.org/html/elife_author_instructions.html">Author Guide</a>.'),
            new Paragraph('*"<a href="https://doi.org/10.7554/eLife.05770">The pleasure of publishing</a>”, by Vivek Malhotra and Eve Marder'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://www.youtube.com/watch?v=3pjXfgeOCho">eLife peer review: The author’s perspective</a> (video)',
                    '<a href="https://www.youtube.com/watch?v=quCG17jZW-w">eLife: Changing the review process</a> (video)',
                    '<a href="https://doi.org/10.7554/eLife.05770">The pleasure of publishing</a>',
                    '<a href="https://doi.org/10.7554/eLife.11326">What makes an eLife paper in epidemiology and global health?</a>',
                    '<a href="https://submit.elifesciences.org/html/elife_author_instructions.html">Author Guide and Policies</a>',
                    '<a href="'.$this->get('router')->generate('alerts').'">Sign up for alerts and news</a>',
                ], 'bullet'))
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function opennessAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Openness';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'We believe that open access to research findings and associated data has the potential to revolutionise the scientific enterprise');

        $arguments['body'] = [
            new Paragraph('Having free and open access to the outcomes of research helps make achievements more visible, accessible and usable – ultimately accelerating discoveries and their applications.'),
            new Paragraph('At eLife, we are actively working to promote openness by:'),
            new DefinitionList([
                'Providing open access to research results' => 'We publish using the <a href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution</a> (CC-BY) license so that users can read, download and reuse text and data for free – provided the authors are given appropriate credit. We also distribute content to digital repositories and other networks.',
                'Promoting understanding of research' => 'For selected papers, we prepare non-technical summaries (<a href="'.$this->get('router')->generate('magazine').'">eLife digests</a>) so that the research we publish is accessible to a broader audience, including scientists in other fields, students, funders, policy makers and others.',
                'Supporting reproducibility' => 'eLife authors are encouraged to publish their work in full and are required to provide the key underlying data as part of their paper. We support projects such as Research Resource Identifiers (RRIDs) that promote unambiguous identification of reagents and materials. eLife is also the publishing partner for the <a href="'.$this->get('router')->generate('collection', ['id' => '9b1e83d1']).'">Reproducibility Project in Cancer Biology</a>.',
                'Making our software open-source' => 'The tools we develop to support research communication, including <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '0414db99']).'">eLife Lens</a> and <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '33e4127f']).'">Continuum</a>, are made openly available so that others can use and build upon them without constraint.',
            ]),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="https://creativecommons.org/licenses/by/4.0/">Our copyright license: Creative Commons Attribution (CC-BY)</a>',
                    '<a href="https://medium.com/@elife">eLife on medium.com</a>',
                    '<a href="http://www.budapestopenaccessinitiative.org/">The Budapest Open Access Initiative (BOAI)</a>',
                ], 'bullet'))
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function innovationAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Innovation';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'eLife invests in open-source technology to deliver effective solutions to accelerate research communication and discovery');

        $arguments['body'] = [
            new Paragraph('eLife invests heavily in software development, new product design, collaboration and outreach so that the potential for improvements in the digital communication of new research can start to be realised. We support the development of open-source tools, with extensible capabilities, that can be used, adopted and modified by any interested party to help move towards an ecosystem that serves science and scientists as efficiently and as cost-effectively as possible.'),
            new Paragraph('The eLife Innovation Initiative is a separately funded effort aimed at accelerating the development of technology and process innovations from creative individuals and teams within the academic and technology industries. The primary outputs of the Initiative are open tools, technologies and processes aimed at improving the discovery, sharing, consumption and evaluation of scientific research.'),
            new Paragraph('Through this Initiative, we’re always on the lookout for opportunities to engage with the best emerging talent and ideas at the interface of research and technology. You can find out more about some of these engagements on <a href="'.$this->get('router')->generate('labs').'">eLife Labs</a>, or contact our Innovation Officer for more information (<a href="mailto:innovation@elifesciences.org">innovation@elifesciences.org</a>).'),
            ArticleSection::basic('Related links', 2,
                $this->render(Listing::unordered([
                    '<a href="'.$this->get('router')->generate('alerts').'">Sign up for Technology & Innovation News from eLife</a>',
                ], 'bullet'))
            ),
        ];

        return new Response($this->get('templating')->render('::about.html.twig', $arguments));
    }

    public function earlyCareerAction(Request $request) : Response
    {
        $arguments = $this->aboutPageArguments($request);

        $arguments['title'] = 'Early-careers';

        $arguments['contentHeader'] = new ContentHeader($arguments['title'], null,
            'The community behind eLife wants to help address some of the pressures on early-career scientists');

        $arguments['body'] = [
            new Paragraph('The community behind eLife – including the research funders who support the journal, the editors and referees who run the peer-review process, and our Early-Career Advisory Group – are keenly aware of the pressures faced by early-stage investigators. That’s one reason we’re working to create a more positive publishing experience that will, among other things, help early-career researchers receive the recognition they deserve.'),
            new Paragraph('eLife supports and showcases early-career scientists and their work in a number of ways:'),
            new DefinitionList([
                'Early-Career Advisory Group' => $this->render(
                    new Paragraph('eLife has invited a group of talented graduate students, postdocs and junior group leaders from across the world to our <a href="'.$this->get('router')->generate('about-people', ['type' => 'early-career']).'">Early-Career Advisory Group</a>. The ECAG acts as a voice for early-career researchers (ECRs) within eLife, representing their needs and aspirations and helping to develop new initiatives and shape current practices to change academic publishing for the better.'),
                    new Paragraph('The role of the ECAG includes:'),
                    Listing::unordered([
                        'Offering ideas and advice on new and ongoing efforts with the potential to help early-career scientists',
                        'Providing direct support for ongoing programs, such as monthly webinars',
                        'Leading efforts to reach out to early-stage colleagues, to gather their feedback and/or connect them to the network',
                        'Participating in online or in-person events about issues of concern to early-stage researchers',
                        'Attending quarterly phone calls and an annual in-person meeting',
                    ], 'bullet'),
                    new Paragraph('For more information, take a look at this <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '140901c3']).'">video message from the ECAG</a>.')
                ),
                'Community Ambassadors' => 'We convene and facilitate a worldwide community of like-minded researchers, led by the ECAG. The eLife Community Ambassadors champion responsible behaviours in science among colleagues and create and deliver solutions that accelerate positive changes in scholarly culture.',
                'Involvement in peer review' => 'eLife encourages reviewers to involve junior colleagues as co-reviewers; we involve outstanding early-stage researchers as reviewers <a href="'.$this->get('router')->generate('inside-elife-article', ['id' => '31a5173b']).'">in their own right</a>; and we enable all reviewers to receive credit for their contributions through services such as Publons and ORCID',
                'Travel grants' => 'eLife offers funding to help early-career scientists get exposure and recognition for their work among leading scientists in their fields. New travel grant programmes are announced at the start of each year. Sign up to our <a href="https://crm.elifesciences.org/crm/community-news">early-career newsletter</a> for updates and information on how to apply.',
                'Webinars' => 'A platform for the early-career community to share opportunities and explore issues around building a successful research career, on the last Wednesday of the month. Previous webinars can be found on our <a href="'.$this->get('router')->generate('collection', ['id' => '842f35d5']).'">collection page</a>.',
                'Magazine features' => 'Early-career researchers and issues of concern to them are regularly featured in interviews, podcasts and articles in the <a href="'.$this->get('router')->generate('magazine').'">Magazine section</a> of eLife',
            ]),
            new Paragraph('For the latest in our work to support early-career scientists, explore our <a href="'.$this->get('router')->generate('community').'">Community page</a> and sign up for eLife <a href="https://crm.elifesciences.org/crm/community-news">News for Early-Career Researchers</a>. You can also find us on Twitter: <a href="https://twitter.com/eLifeCommunity">@eLifeCommunity</a>'),
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
                $foundingEditorInChief = $leadership->filter(function (Person $person) {
                    return 'Founding Editor-in-Chief' === $person->getTypeLabel();
                });
                $deputyEditors = $leadership->filter(function (Person $person) {
                    return !in_array($person->getTypeLabel(), ['Editor-in-Chief', 'Founding Editor-in-Chief']);
                });

                $arguments['lists'][] = $this->createAboutProfiles($editorInChief, 'Editor-in-Chief');
                $arguments['lists'][] = $this->createAboutProfiles($deputyEditors, 'Deputy editors');
                $arguments['lists'][] = $this->createAboutProfiles($people->forType('senior-editor'), 'Senior editors');
                $arguments['lists'][] = $this->createAboutProfiles($foundingEditorInChief, 'Founding Editor-in-Chief');
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
                $impactStatement = $parts['impactStatement'] ?? 'The working scientists who serve as eLife editors, our early-career advisors, governing board, and our executive staff all work in concert to realise eLife’s mission to accelerate discovery.';

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
            'Openness' => $this->get('router')->generate('about-openness'),
            'Innovation' => $this->get('router')->generate('about-innovation'),
            'Early-careers' => $this->get('router')->generate('about-early-career'),
        ];

        $currentPath = $this->get('router')->generate($request->attributes->get('_route'), $request->attributes->get('_route_params'));

        $menuItems = array_map(function (string $text, string $path) use ($currentPath) {
            return new Link($text, $path, $path === $currentPath);
        }, array_keys($menuItems), array_values($menuItems));

        $arguments['menu'] = new SectionListing('sections', $menuItems, new ListHeading('About sections'), true);

        return $arguments;
    }
}
