<?php

namespace test\eLife\Journal\Helper;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\Journal\Helper\CanBuildAssessment;
use PHPUnit\Framework\TestCase;

class CanBuildAssessmentTest extends TestCase
{
    public function testReturnStateAnonymous(): void
    {
        $controller = new class {
            use CanBuildAssessment;
        };
        $content = new ArraySequence([
            new Paragraph("This <b>valuable</b> paper compares blood gene signature responses between small cohorts of individuals with mild and severe COVID-19. The authors provide <b>solid</b> evidence for distinct transcriptional profiles during early COVID-19 infections that may be predictive of severity, within the limitations of studying human patients displaying heterogeneity in infection timelines and limited cohort size.")
        ]);
        $doi = '10.7554/eLife.94242.3.sa0';
        $id = 'sa0';
        $elifeAssessment = new ArticleSection($content, $doi, $id);
        $result = $controller->buildAssessmentViewModel($elifeAssessment);

        $this->assertInstanceOf('eLife\Patterns\ViewModel\Term', $result['significance']);
        $this->assertContains('<b>Valuable</b>', $result['significance']['termDescription']);
        $this->assertEquals('Valuable', $result['significance']['terms'][3]['term']);
        $this->assertTrue($result['significance']['terms'][3]['isHighlighted']);

        $this->assertInstanceOf('eLife\Patterns\ViewModel\Term', $result['strength']);
        $this->assertContains('<b>Solid</b>', $result['strength']['termDescription']);
        $this->assertEquals('Solid', $result['strength']['terms'][3]['term']);
        $this->assertTrue($result['strength']['terms'][3]['isHighlighted']);
    }
}
