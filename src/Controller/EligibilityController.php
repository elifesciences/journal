<?php

namespace eLife\Journal\Controller;

use eLife\Patterns\ViewModel\CompactForm;
use eLife\Patterns\ViewModel\ContentHeader;
use eLife\Patterns\ViewModel\ContentHeaderSimple;
use eLife\Patterns\ViewModel\Form;
use eLife\Patterns\ViewModel\Input;
use eLife\Patterns\ViewModel\InstitutionEligibilityChecker;
use eLife\Patterns\ViewModel\InstitutionEligibilityOutcome;
use eLife\Patterns\ViewModel\InstitutionSearchResults;
use eLife\Patterns\ViewModel\Link;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EligibilityController extends Controller
{
    /*
     * Three possible scenarios for the outcomes of the eligibility tool
     *
     * 1. The institution HAS signed an agreement with eLife
     * 2. The institution does NOT have and agreement signed but people from the same institution and/or funder have
     *      already published in eLife in the past couple of years
     * 3. The institution does NOT have an agreement signed AND no one from the same institution/funder has published
     */

    private const INSTITUTIONS = [

    ];

    public function indexAction(Request $request)
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments ['title'] = 'eLife Eligibility Tool';
        $arguments['checker'] = new InstitutionEligibilityChecker(
            'test label',
            'test placeholder',
            'cta string',
            '/eligibility/search',
            '',
            null,
            null,
            (new CompactForm(
                new Form('/eligibility/search', 'eligibility_search', 'GET'),
                new Input('Choose your institution:', 'search', 'institution', '', 'Enter text here'),
                'Search'
            ))->withVisibleLabel()->withVariant(CompactForm::VARIANT_INSTITUTION_ELIGIBILITY)
        );

        $arguments['contentHeader'] = (new ContentHeaderSimple(
            'Check your eligibility',
            'eLife has publishing agreements with more than x institutions that cover publishing fees for affiliated
                researchers. Check your institution below, or see the full list of institutions.'
        ));

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    public function checkAction(Request $request, string $institution)
    {
//        $type = $this->get('elife.journal.institution_eligibility_checker')->check($institution);

        #TODO don't show the checker again. Show another page with the outcomes

        $arguments = $this->defaultPageArguments($request);

        $agreementSigned = false;
        foreach (self::INSTITUTIONS as $candidate) {
            if ($candidate['name'] === $institution) {
                $agreementSigned = $candidate['agreementSigned'];
                break;
            }
        }

        // TODO add the check for the third type too
        if ($agreementSigned) {
            $type = InstitutionEligibilityOutcome::TYPE_AGREED;
        } else {
            $type = InstitutionEligibilityOutcome::TYPE_NOT_AGREED_PUBLISHED;
        }

        $arguments ['title'] = 'eLife Eligibility Tool';
        #TODO add the outcome props to the checker
        $arguments['outcome'] = new InstitutionEligibilityOutcome($type);

        $arguments['contentHeader'] = (new ContentHeaderSimple(
            'Check your eligibility',
            'eLife has publishing agreements with more than x institutions that cover publishing fees for affiliated
                researchers. Check your institution below, or see the full list of institutions.'
        ));

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    public function searchAction(Request $request)
    {
        $search = $request->query->get('institution', '');

        $searchResults = [];

        if (!empty($search)) {
            foreach (self::INSTITUTIONS as $candidate) {
                if (strpos(strtolower($candidate['name']), strtolower($search)) !== false) {
                    $searchResults[] = new Link(
                        $candidate['name'],
                        "/eligibility/check/{$candidate['name']}"
                    );
                }
            }
        }

        $searchResultsObject = new InstitutionSearchResults(
            $searchResults,
            'No institutions found'
        );

        if ($request->isXmlHttpRequest()) {
            return new Response($this->render($searchResultsObject));
        } else {
            $arguments = $this->defaultPageArguments($request);

            $arguments ['title'] = 'eLife Eligibility Tool';

            $arguments['contentHeader'] = (new ContentHeaderSimple(
                'Check your eligibility',
                'eLife has publishing agreements with more than x institutions that cover publishing fees for affiliated
                researchers. Check your institution below, or see the full list of institutions.'
            ));

            $arguments['checker'] = new InstitutionEligibilityChecker(
                'test label',
                'test placeholder',
                'cta string',
                '/eligibility/search',
                $search,
                $searchResultsObject,
                null,
                (new CompactForm(
                    new Form('/eligibility/search', 'eligibility_search', 'GET'),
                    new Input('Choose your institution:', 'search', 'institution', $search, 'Enter text here'),
                    'Search'
                ))->withVisibleLabel()->withVariant(CompactForm::VARIANT_INSTITUTION_ELIGIBILITY)
            );   // whole page, outcome pre-filled

            return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
        }
    }
}
