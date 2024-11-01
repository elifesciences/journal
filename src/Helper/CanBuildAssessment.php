<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\Patterns\ViewModel\Assessment;
use eLife\Patterns\ViewModel\Term;

trait CanBuildAssessment
{
    private static $termDescriptions = [
        'landmark' => 'Findings with profound implications that are expected to have widespread influence',
        'fundamental' => 'Findings that substantially advance our understanding of major research questions',
        'important' => 'Findings that have theoretical or practical implications beyond a single subfield',
        'valuable' => 'Findings that have theoretical or practical implications for a subfield',
        'useful' => 'Findings that have focused importance and scope',
        'exceptional' => 'Exemplary use of existing approaches that establish new standards for a field',
        'compelling' => 'Evidence that features methods, data and analyses more rigorous than the current state-of-the-art',
        'convincing' => 'Appropriate and validated methodology in line with current state-of-the-art',
        'solid' => 'Methods, data and analyses broadly support the claims with only minor weaknesses',
        'incomplete' => 'Main claims are only partially supported',
        'inadequate' => 'Methods, data and analyses do not support the primary claims',
    ];

    final public function buildAssessmentViewModel(ArticleSection $elifeAssessment): Assessment {
        $summary = 'During the peer-review process the editor and reviewers write an eLife Assessment that summarises the significance of the findings reported in the article (on a scale ranging from landmark to useful) and the strength of the evidence (on a scale ranging from exceptional to inadequate). <a href="https://elifesciences.org/about/elife-assessments">Learn more about eLife Assessments</a>';
        $significanceTerms = ['Landmark', 'Fundamental', 'Important', 'Valuable', 'Useful'];
        $strengthTerms = ['Exceptional', 'Compelling', 'Convincing', 'Solid', 'Incomplete', 'Inadequate'];
        $content = $elifeAssessment->getContent();
        $resultSignificance = $this->highlightAndFormatTerms($content, $significanceTerms);
        $resultStrength = $this->highlightAndFormatTerms($content, $strengthTerms);
        $significanceAriaLabel = 'eLife assessments use a common vocabulary to describe significance. The term chosen for this paper is:';
        $strengthAriaLabel = 'eLife assessments use a common vocabulary to describe strength of evidence. The term or terms chosen for this paper is:';
        $significance = !empty($resultSignificance['formattedDescription'])
            ? new Term(
                'Significance of the findings:',
                implode(PHP_EOL, $resultSignificance['formattedDescription']),
                $resultSignificance['highlightedTerm'],
                $significanceAriaLabel
            )
            : null;
        $strength = !empty($resultStrength['formattedDescription'])
            ? new Term(
                'Strength of evidence:',
                implode(PHP_EOL, $resultStrength['formattedDescription']),
                $resultStrength['highlightedTerm'],
                $strengthAriaLabel
            )
            : null;

        return new Assessment(
            $significance,
            $strength,
            $summary
        );
    }

    private function highlightAndFormatTerms(Sequence $content, array $availableTerms): array
    {
        $highlightedWords = $this->extractHighlightedWords($content);
        $matchingNormalisedTerms = $this->normaliseTerms($highlightedWords);

        return [
            'highlightedTerm' => $this->highlightFoundTerms($matchingNormalisedTerms, $availableTerms),
            'formattedDescription' => $this->formatTermDescriptions($matchingNormalisedTerms, $availableTerms),
        ];
    }

    private function extractHighlightedWords(Sequence $content): array
    {
        $highlightedWords = [];

        foreach ($content as $contentItem) {
            if (method_exists($contentItem, 'getText')) {
                $text = $contentItem->getText();

                preg_match_all('/<b>(.*?)<\/b>/', $text, $matches);

                if (!empty($matches[1])) {
                    $highlightedWords = array_merge($highlightedWords, $matches[1]);
                }
            }
        }

        return $highlightedWords;
    }

    private function normaliseTerms(array $words): array
    {
        $variations = [
            'convincingly' => 'convincing',
            'inadequately' => 'inadequate',
            'incompletely' => 'incomplete',
        ];

        foreach ($variations as $variation => $normalisedTerm) {
            if (in_array($variation, $words)) {
                $words[] = $normalisedTerm;
            }
        }
        return $words;
    }

    private function highlightFoundTerms(array $highlightedWords, array $availableTerms)
    {
        return array_map(function (string $term) use ($highlightedWords) {
            $termWord = strtolower($term);
            $termViewModel = ['term' => $term];

            if (in_array($termWord, $highlightedWords)) {
                $termViewModel['isHighlighted'] = true;
            }

            return $termViewModel;
        }, $availableTerms);
    }

    private function formatTermDescriptions($highlightedWords, $availableTerms): array
    {
        $matchingTerms = array_filter($availableTerms, function (string $term) use ($highlightedWords) {
            $termWord = strtolower($term);

            if (in_array($termWord, $highlightedWords)) {
                if (isset(self::$termDescriptions[$termWord])) {
                    return true;
                }
            }
            return false;
        });

        $formattedDescription = array_map(function (string $term) {
            return sprintf(
                "<p><b>%s</b>: %s</p>",
                $term,
                self::$termDescriptions[strtolower($term)]
            );
        }, $matchingTerms);

        return $formattedDescription;
    }
}
