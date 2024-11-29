<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\Patterns\ViewModel\ArticleAssessmentTerms;
use eLife\Patterns\ViewModel\Assessment;
use eLife\Patterns\ViewModel\Term;

trait CanCreateAssessment
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

    final public function createAssessment(ArticleSection $elifeAssessment): Assessment {
        $summary = 'During the peer-review process the editor and reviewers write an eLife Assessment that summarises the significance of the findings reported in the article (on a scale ranging from landmark to useful) and the strength of the evidence (on a scale ranging from exceptional to inadequate). <a href="https://elifesciences.org/about/elife-assessments">Learn more about eLife Assessments</a>';
        $significanceTerms = ['Landmark', 'Fundamental', 'Important', 'Valuable', 'Useful'];
        $strengthTerms = ['Exceptional', 'Compelling', 'Convincing', 'Solid', 'Incomplete', 'Inadequate'];
        $content = $elifeAssessment->getContent();
        $resultSignificance = $this->highlightAndFormatTerms($content, $significanceTerms);
        $resultStrength = $this->highlightAndFormatTerms($content, $strengthTerms);
        $significanceAriaLabel = 'eLife assessments use a common vocabulary to describe significance. The term chosen for this paper is:';
        $strengthAriaLabel = 'eLife assessments use a common vocabulary to describe strength of evidence. The term or terms chosen for this paper is:';
        $significance = !empty($resultSignificance['formattedDescription'])
            ? new ArticleAssessmentTerms(
                'Significance of the findings:',
                implode(PHP_EOL, $resultSignificance['formattedDescription']),
                $resultSignificance['highlightedTerm'],
                $significanceAriaLabel
            )
            : null;
        $strength = !empty($resultStrength['formattedDescription'])
            ? new ArticleAssessmentTerms(
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
        $emboldenedWords = $this->extractEmboldenedWords($content);
        $matchingTerms = $this->findMatchingTerms($emboldenedWords);

        return [
            'highlightedTerm' => $this->highlightFoundTerms($matchingTerms, $availableTerms),
            'formattedDescription' => $this->formatTermDescriptions($matchingTerms, $availableTerms),
        ];
    }

    private function extractEmboldenedWords(Sequence $content): array
    {
        $emboldenedWords = [];

        foreach ($content as $contentItem) {
            if (method_exists($contentItem, 'getText')) {
                $text = $contentItem->getText();

                preg_match_all('/<b>(.*?)<\/b>/', $text, $matches);

                if (!empty($matches[1])) {
                    $emboldenedWords = array_merge($emboldenedWords, $matches[1]);
                }
            }
        }

        return $emboldenedWords;
    }

    private function findMatchingTerms(array $words): array
    {
        $availableTerms = array_keys(self::$termDescriptions);
        $matchingTerms = [];
        $variationToTerm = [
            'convincingly' => 'convincing',
            'inadequately' => 'inadequate',
            'incompletely' => 'incomplete',
        ];

        foreach ($words as $word) {
            $normalisedWord = strtolower($word);
            if (array_key_exists($normalisedWord, $variationToTerm)) {
                $matchingTerms[] = $variationToTerm[$normalisedWord];
            } elseif (in_array($normalisedWord, $availableTerms)) {
                $matchingTerms[] = $normalisedWord;
            }
        }
        return $matchingTerms;
    }

    private function highlightFoundTerms(array $foundTerms, array $availableTerms)
    {
        return array_map(
            function (string $termValue) use ($foundTerms) {
                $termWord = strtolower($termValue);
                $isHighlighted = in_array($termWord, $foundTerms);
                return new Term($termValue, $isHighlighted);
            },
            $availableTerms
        );
    }

    private function formatTermDescriptions($matchingTerms, $availableTerms): array
    {
        $matchingTerms = array_filter($availableTerms, function (string $term) use ($matchingTerms) {
            $termWord = strtolower($term);

            if (in_array($termWord, $matchingTerms)) {
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
