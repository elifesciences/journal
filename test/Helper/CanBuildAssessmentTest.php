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
