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
use eLife\Patterns\ViewModel\Breadcrumb;
use eLife\Patterns\ViewModel\Button;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\FormLabel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\ListHeading;
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
            ->append(new SelectOption('ethics-committee', 'Ethics committee', 'ethics-committee' === $type));
            // SA 26/10/23: remove staff page from about pages
            // ->append(new SelectOption('staff', 'Executive staff', 'staff' === $type));

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
            // SA 26/10/23: remove staff page from about pages
            // case 'staff':
            //     $arguments['title'] = 'Executive staff';
            //
            //     $arguments['lists'][] = $this->createAboutProfiles($people->forType('executive'), 'Executive staff');
            //     break;
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

                return new ContentHeader($parts['title'], null, $impactStatement, false,
                    new Breadcrumb([
                        new Link(
                            'About',
                            $this->get('router')->generate('about')
                        ),
                        new Link(
                            'Editors and people'
                        ),
                    ]),
                    [], null, null, null, null,
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
        return $this->defaultPageArguments($request);
    }
}
