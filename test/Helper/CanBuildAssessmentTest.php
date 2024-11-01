<?php

namespace test\eLife\Journal\Helper;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\Journal\Helper\CanBuildAssessment;
use eLife\Patterns\ViewModel\Assessment;
use PHPUnit\Framework\TestCase;

class CanBuildAssessmentTest extends TestCase
{
    /**
     * @test
     */
    public function it_highlights_a_statement_with_valuable_significance_and_with_solid_strength(): void
    {
        $assessmentText = "This <b>valuable</b> paper compares blood gene signature responses between small cohorts of individuals with mild and severe COVID-19. The authors provide <b>solid</b> evidence for distinct transcriptional profiles during early COVID-19 infections that may be predictive of severity, within the limitations of studying human patients displaying heterogeneity in infection timelines and limited cohort size.";
        
        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Valuable', $result);
        $this->assertHasStrength('Solid', $result);
    }

    /**
     * @test
     */
    public function it_does_not_highlight_terms_that_are_not_mentioned_in_the_statement(): void
    {
        $assessmentText = "This <b>valuable</b> paper compares blood gene signature responses between small cohorts of individuals with mild and severe COVID-19. The authors provide <b>solid</b> evidence for distinct transcriptional profiles during early COVID-19 infections that may be predictive of severity, within the limitations of studying human patients displaying heterogeneity in infection timelines and limited cohort size.";

        $result = $this->getTestResult($assessmentText);

        $notHighlightedSignificanceTerms = array_filter($result['significance']['terms'], function (array $term) {
            return $term['term'] !== 'Valuable';
        });
        foreach ($notHighlightedSignificanceTerms as $each) {
            $this->assertArrayNotHasKey('isHighlighted', $each);
        }
        $notHighlightedStrengthTerms = array_filter($result['strength']['terms'], function (array $term) {
            return $term['term'] !== 'Solid';
        });
        foreach ($notHighlightedStrengthTerms as $each) {
            $this->assertArrayNotHasKey('isHighlighted', $each);
        }
    }

    /**
     * @test
     */
    public function it_does_not_highlight_a_statement_that_does_not_contain_at_least_one_emboldened_term(): void
    {
        $assessmentText = "This valuable paper compares blood gene signature responses between small cohorts of individuals with mild and severe COVID-19. The authors provide solid evidence for distinct transcriptional profiles during early COVID-19 infections that may be predictive of severity, within the limitations of studying human patients displaying heterogeneity in infection timelines and limited cohort size.";

        $result = $this->getTestResult($assessmentText);

        $this->assertNull($result['significance']);
        $this->assertNull($result['strength']);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_valuable_significance_in_uppercase(): void
    {
        $assessmentText = "<b>Valuable</b> paper that compares ...";

        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Valuable', $result);
    }

    /**
     * @test
     */
    public function it_does_not_highlight_a_statement_with_a_bold_word_that_is_not_a_term(): void
    {
        $assessmentText = "<b>Exactly</b> one paper that compares ...";

        $result = $this->getTestResult($assessmentText);

        $this->assertNull($result['significance']);
        $this->assertNull($result['strength']);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_important_significance_and_with_convincing_strength(): void
    {
        $assessmentText = "This <b>important</b> manuscript shows that axonal transport of Wnd is required for its normal degradation by the Hiw ubiquitin ligase pathway. In Hiw mutants, the Wnd protein accumulates in nerve terminals. In the absence of axonal transport, Wnd levels also rise and lead to excessive JNK signaling, disrupting neuronal function. These are interesting findings supported by <b>convincing</b> data. However, how Rab11 is involved in Golgi processing or axonal transport of Wnd is not resolved as it is clear that Rab11 is not travelling with Wnd to the axon.";
        
        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Important', $result);
        $this->assertHasStrength('Convincing', $result);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_multiple_strength_terms(): void
    {
        $assessmentText = "This study elucidates the role of a specific hemocyte subpopulation in oxidative damage response by establishing connections between DNA damage response and the JNK-JAK/STAT axis to regulate energy metabolism. The identification of this distinct hemocyte subpopulation through single-cell RNA sequencing analysis and the finding of hemocytes that respond to oxidative stress are <b>important</b>. The method for single-cell RNA sequencing and related analyses are <b>convincing</b> and experiments linking oxidative stress to DNA damage and energy expenditure are <b>solid</b>. The finding of stress-responsive immune cells capable of influencing whole-body metabolism adds insights for cell biologists and developmental biologists in the fields of immunology and metabolism.";

        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Important', $result);
        $this->assertHasStrength('Convincing', $result);
        $this->assertHasStrength('Solid', $result);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_a_term_variation_for_convincing_strength(): void
    {
        $assessmentText = "This manuscript reports <b>important</b> in vitro biochemical and in planta experiments to study the receptor activation mechanism of plant membrane receptor kinase complexes through the non-catalytic function of an active protein kinase. Several lines of evidence <b>convincingly</b> show that one such receptor kinase with pseudokinase-like function, the immune receptor EFR achieves an active conformation following phosphorylation by a co-receptor kinase, and then in turn activates the co-receptor kinase allosterically to enable it to phosphorylate down-stream signaling components. This manuscript will be of interest to scientists focusing on cell signalling and allosteric regulation.";

        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Important', $result);
        $this->assertHasStrength('Convincing', $result);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_a_term_variation_for_inadequate_strength(): void
    {
        $assessmentText = "The authors show MRI relaxation time changes that are claimed to originate from cell membrane potential changes. This would be very <b>important</b> if true because it may provide a mechanism whereby membrane potential changes could be inferred noninvasively. However, the membrane potential manipulations applied here will induce cell swelling, and cell swelling has been previously shown to affect relaxation time. Therefore, the claim that the relaxation time changes observed in this manuscript are due to cell membrane potential changes is <b>inadequately</b> supported.";

        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Important', $result);
        $this->assertHasStrength('Inadequate', $result);
    }

    /**
     * @test
     */
    public function it_highlights_a_statement_with_a_term_variation_for_incomplete_strength(): void
    {
        $assessmentText = "This study uses C. elegans, a poikilothermic (\"cold-blooded\") animal, to investigate the interesting question of how cells and organisms adapt to prolonged exposure to cold temperature. The study employed ribosome profiling and RNAseq analyses and provides a <b>useful</b> inventory of genes changed in cold adapted nematodes. However, the overall conclusions that 1) translation is ongoing at a low rate and 2) IRE mediated transcriptional changes play a significant role in cold adaptation are <b>incompletely</b> supported by the evidence provided. The authors are encouraged to conduct additional bioinformatic analyses and rewrite the manuscript to more accurately reflect the evidence provided.";

        $result = $this->getTestResult($assessmentText);

        $this->assertHasSignificance('Useful', $result);
        $this->assertHasStrength('Incomplete', $result);
    }

    private function getTestResult(string $contentText)
    {
        $controller = new class {
            use CanBuildAssessment;
        };
        $content = new ArraySequence([
            new Paragraph($contentText)
        ]);
        $doi = '10.7554/eLife.94242.3.sa0';
        $id = 'sa0';
        $elifeAssessment = new ArticleSection($content, $doi, $id);
        return $controller->buildAssessmentViewModel($elifeAssessment);
    }

    private function assertHasSignificance(string $term, Assessment $result)
    {
        $this->assertInstanceOf('eLife\Patterns\ViewModel\Term', $result['significance']);
        $this->assertContains("<b>{$term}</b>", $result['significance']['termDescription']);
        $highlightedTerm = ['term' => $term, 'isHighlighted' => true];
        $this->assertContains($highlightedTerm, $result['significance']['terms']);
    }

    private function assertHasStrength(string $term, Assessment $result)
    {
        $this->assertInstanceOf('eLife\Patterns\ViewModel\Term', $result['strength']);
        $this->assertContains("<b>{$term}</b>", $result['strength']['termDescription']);
        $highlightedTerm = ['term' => $term, 'isHighlighted' => true];
        $this->assertContains($highlightedTerm, $result['strength']['terms']);
    }
}
