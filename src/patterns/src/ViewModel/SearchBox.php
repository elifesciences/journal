<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SearchBox implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $compactForm;
    private $subjectFilter;

    public function __construct(CompactForm $compactForm, SubjectFilter $subjectFilter = null)
    {
        $this->compactForm = $compactForm;
        $this->subjectFilter = $subjectFilter;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/search-box.mustache';
    }
}
