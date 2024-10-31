<?php

namespace eLife\Journal\Helper;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\Patterns\ViewModel\Assessment;
use eLife\Patterns\ViewModel\Term;

trait CanBuildAssessment
{
    final public function buildAssessmentViewModel(ArticleSection $elifeAssessment): Assessment {
        $summary = 'During the peer-review process the editor and reviewers write an eLife Assessment that summarises the significance of the findings reported in the article (on a scale ranging from landmark to useful) and the strength of the evidence (on a scale ranging from exceptional to inadequate). <a href="https://elifesciences.org/about/elife-assessments">Learn more about eLife Assessments</a>';
        $significanceTerms = [['term' => 'Landmark'], ['term' => 'Fundamental'], ['term' => 'Important'], ['term' => 'Valuable'], ['term' => 'Useful']];
        $strengthTerms = [['term' => 'Exceptional'], ['term' => 'Compelling'], ['term' => 'Convincing'], ['term' => 'Solid'], ['term' => 'Incomplete'], ['term' => 'Inadequate']];
        $content = $elifeAssessment->getContent();
        $resultSignificance = $this->highlightAndFormatTerms($content, $significanceTerms);
        $resultStrength = $this->highlightAndFormatTerms($content, $strengthTerms);
        $significanceAriaLable = 'eLife assessments use a common vocabulary to describe significance. The term chosen for this paper is:';
        $strengthAriaLable = 'eLife assessments use a common vocabulary to describe strength of evidence. The term or terms chosen for this paper is:';
        $significance = !empty($resultSignificance['formattedDescription']) ? new Term('Significance of the findings:', implode(PHP_EOL, $resultSignificance['formattedDescription']), $resultSignificance['highlightedTerm'], $significanceAriaLable) : null;
        $strength = !empty($resultStrength['formattedDescription']) ? new Term('Strength of evidence:', implode(PHP_EOL, $resultStrength['formattedDescription']), $resultStrength['highlightedTerm'], $strengthAriaLable) : null;

        return new Assessment(
            $significance,
            $strength,
            $summary
        );
    }

    private function highlightAndFormatTerms(Sequence $content, array $terms): array {
        $formattedDescription = [];
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

        $highlightedTerm = array_map(function ($term) use ($highlightedWords, &$formattedDescription) {
            $termDescriptions = [
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

            $termWord = strtolower($term['term']);

            if (in_array($termWord, $highlightedWords)) {
                $term['isHighlighted'] = true;

                if (isset($termDescriptions[$termWord])) {
                    $formattedDescription[$termWord] = sprintf("<p><b>%s</b>: %s</p>", ucfirst($termWord), $termDescriptions[$termWord]);
                }
            }

            return $term;
        }, $terms);

        return [
            'highlightedTerm' => $highlightedTerm,
            'formattedDescription' => $formattedDescription
        ];
    }
}
