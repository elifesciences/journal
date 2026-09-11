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

    /**
     * @var ContentHeaderSimple
     */
    private $contentHeader;

    private $title;

    private const INSTITUTIONS = [

    ];

    public function __construct()
    {
        $this->contentHeader = new ContentHeaderSimple(
            'Check your eligibility',
            'eLife has publishing agreements with more than x institutions that cover publishing fees for affiliated
                researchers. Check your institution below, or see the <a href="#">full list of institutions.</a>'
        );

        $this->title = 'eLife Eligibility Tool';
    }

    public function indexAction(Request $request)
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments ['title'] = $this->title;
        $arguments['checker'] = new InstitutionEligibilityChecker(
            (new CompactForm(
                new Form('/eligibility/search', 'eligibility_search', 'GET'),
                new Input('Choose your institution:', 'search', 'institution', '', 'Enter text here'),
                'Search'
            ))->withVisibleLabel()
                ->withVariant(CompactForm::VARIANT_INSTITUTION_ELIGIBILITY)
                ->withAutocompleteOff(),
            null,
            null,
            '/eligibility.json'
        );

        $arguments['contentHeader'] = $this->contentHeader;

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    public function checkAction(Request $request, string $institution)
    {
        $arguments = $this->defaultPageArguments($request);

        $eligibility_json = $this->getInstitutions();

        $agreementSigned = null;
        if (!empty($institution)) {
            foreach ($eligibility_json as $candidate) {
                if (strtolower($candidate['name']) === strtolower($institution)) {
                    $agreementSigned = $candidate['has-deal'];
                    break;
                }
            }
        }

        if ($agreementSigned) {
            $type = InstitutionEligibilityOutcome::TYPE_AGREED;
        } else {
            $type = InstitutionEligibilityOutcome::TYPE_NOT_AGREED_PUBLISHED;
        }

        $arguments ['title'] = $this->title;

        $arguments['outcome'] = new InstitutionEligibilityOutcome($type, $institution);

        $arguments['contentHeader'] = $this->contentHeader;

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $query = $request->query->get('institution', '');

        $searchResults = [];

        $eligibility_json = $this->getInstitutions();

        if (!empty($query)) {
            foreach ($eligibility_json as $candidate) {
                if (strpos(strtolower($candidate['name']), strtolower($query)) !== false) {
                    $urlencodedName = urldecode($candidate['name']);
                    $searchResults[] = new Link(
                        "{$candidate['name']} ({$candidate['city']}, {$candidate['country']})",
                        "/eligibility/check/{$urlencodedName}"
                    );
                }
            }
        }

        $searchResultsObject = new InstitutionSearchResults($searchResults);

        $arguments = $this->defaultPageArguments($request);

        $arguments ['title'] = $this->title;

        $arguments['contentHeader'] = $this->contentHeader;

        $arguments['checker'] = new InstitutionEligibilityChecker(
            (new CompactForm(
                new Form('/eligibility/search', 'eligibility_search', 'GET'),
                new Input('Choose your institution:', 'search', 'institution', $query, 'Enter text here'),
                'Search'
            ))->withVisibleLabel()
                ->withVariant(CompactForm::VARIANT_INSTITUTION_ELIGIBILITY)
                ->withAutocompleteOff(),
            $searchResultsObject,
            null,
            '/eligibility.json'
        );

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    private function getInstitutions(): array
    {
        $eligibility_file = file_get_contents(__DIR__ . '/../../eligibility.json');

        return json_decode($eligibility_file, true);
    }
}
