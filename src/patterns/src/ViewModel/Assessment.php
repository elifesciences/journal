<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Assessment implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $significance;
    private $strength;
    private $summary;

    public function __construct(ArticleAssessmentTerms $significance = null, ArticleAssessmentTerms $strength = null, string $summary = null)
    {
        Assertion::nullOrNotBlank($significance);
        Assertion::nullOrNotBlank($strength);
        Assertion::nullOrNotBlank($summary);

        $this->significance = $significance;
        $this->strength = $strength;
        $this->summary = $summary;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/assessment.mustache';
    }
}
