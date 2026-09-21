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
    /**
     * @var ContentHeaderSimple
     */
    private $contentHeader;

    private $title;

    public function __construct()
    {
        $this->contentHeader = new ContentHeaderSimple(
            'Check your eligibility',
            'eLife has publishing agreements with more than x institutions that cover publishing fees for affiliated
                researchers. Check your institution below, or see the <a href="#">full list of institutions.</a>',
            true
        );

        $this->title = 'eLife Eligibility Tool';
    }

    public function indexAction(Request $request)
    {
        $arguments = $this->defaultPageArguments($request);

        $arguments ['title'] = $this->title;
        $arguments['contentHeader'] = $this->contentHeader;

        $arguments['checker'] = new InstitutionEligibilityChecker(
            (new CompactForm(
                new Form('/eligibility/check', 'eligibility_search', 'GET'),
                new Input('Choose your institution:', 'search', 'institution', '', 'Enter text here'),
                'Search'
            ))->withVisibleLabel()
                ->withVariant(CompactForm::VARIANT_INSTITUTION_ELIGIBILITY)
                ->withAutocompleteOff(),
            null,
            null,
            '/eligibility.json'
        );

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    public function checkAction(Request $request)
    {
        $institution = $request->get('institution');
        $eligibility_json = $this->getInstitutions();

        $arguments = $this->defaultPageArguments($request);
        $arguments['title'] = $this->title;
        $arguments['contentHeader'] = $this->contentHeader;

        $candidate = null;
        if (!empty($institution)) {
            foreach ($eligibility_json as $e) {
                if (strtolower($e['name']) === strtolower($institution)) {
                    $candidate = $e;
                    break;
                }
            }
        }

        if ($candidate === null) {
            $possibleMatches = $this->findPossibleMatches($institution);

            $possibleMatches = array_map(function ($match) {
                return [
                    'name'    => ucfirst($match['name']),
                    'country' => $match['country'],
                    'url'     => '/eligibility/check?institution=' . urlencode($match['name']),
                ];
            }, $possibleMatches);

            $arguments['outcome'] = new InstitutionEligibilityOutcome(
                count($possibleMatches) > 0 ?
                    InstitutionEligibilityOutcome::TYPE_NOT_AGREED_NOT_PUBLISHED_WITH_POSSIBLE_MATCHES :
                    InstitutionEligibilityOutcome::TYPE_NOT_AGREED_NOT_PUBLISHED,
                ucfirst($institution),
                null,
                false,
                $possibleMatches
            );

            return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
        }

        if ($candidate['has-deal']) {
            $type = InstitutionEligibilityOutcome::TYPE_AGREED;
        } else {
            $type = InstitutionEligibilityOutcome::TYPE_NOT_AGREED_PUBLISHED;
        }

        $dealUntil = null;
        $rollingDeal = false;
        if ($candidate['until']) {
            $datetime = new \Datetime($candidate['until']);
            if ($datetime->format('Y') == 3000) {
                $rollingDeal = true;
            } else {
                $dealUntil = $datetime->format('M Y');
            }
        }

        // TODO check for the 10 institutions

        $arguments['outcome'] = new InstitutionEligibilityOutcome(
            $type,
            $institution,
            $dealUntil,
            $rollingDeal
        );

        return new Response($this->get('templating')->render('::institution-eligibility.html.twig', $arguments));
    }

    private function getInstitutions(): array
    {
        $item = $this->get('cache.eligibility')->getItem('institutions');

        if (!$item->isHit()) {
            $eligibility_file = file_get_contents($this->getParameter('eligibility_json_path'));
            $item->set(json_decode($eligibility_file, true));
            $this->get('cache.eligibility')->save($item);
        }

        return $item->get();
    }

    /**
     * Pre-lowercased name + word list for each institution, built once per
     * cache lifetime instead of on every search request.
     */
    private function getSearchIndex(): array
    {
        $item = $this->get('cache.eligibility')->getItem('institutions_search_index_v2');

        if (!$item->isHit()) {
            $index = array_map(function (array $e) {
                $lowerName = strtolower($e['name']);

                return [
                    'name' => $e['name'],
                    'country' => $e['country'],
                    'lower' => $lowerName,
                    'words' => array_filter(preg_split('/[\s,-]+/', $lowerName)),
                ];
            }, $this->getInstitutions());

            $item->set($index);
            $this->get('cache.eligibility')->save($item);
        }

        return $item->get();
    }

    private function findPossibleMatches(string $institution, int $limit = 3): array
    {
        $needle = strtolower(trim($institution));
        $needleLength = strlen($needle);
        $threshold = $needleLength <= 5 ? 1 : ($needleLength <= 8 ? 2 : 3);

        $distances = [];
        $countries = [];
        foreach ($this->getSearchIndex() as $entry) {
            if (strpos($entry['lower'], $needle) !== false) {
                $distances[$entry['name']] = 0;
                $countries[$entry['name']] = $entry['country'];
                continue;
            }

            foreach ($entry['words'] as $word) {
                if (abs(strlen($word) - $needleLength) > 2) {
                    continue;
                }

                $distance = levenshtein($needle, $word);

                if ($distance <= $threshold) {
                    $distances[$entry['name']] = min($distances[$entry['name']] ?? PHP_INT_MAX, $distance);
                    $countries[$entry['name']] = $entry['country'];
                }
            }
        }

        asort($distances);

        return array_map(function ($name) use ($countries) {
            return [
                'name' => $name,
                'country' => $countries[$name],
            ];
        }, array_slice(array_keys($distances), 0, $limit));
    }
}
