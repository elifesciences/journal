<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ComposedViewModel;
use eLife\Patterns\ViewModel;

final class Honeypot implements ViewModel
{
    use ComposedViewModel;

    private $textField;

    public function __construct(TextField $textField)
    {
        $this->textField = $textField;
    }

    protected function getViewModel() : ViewModel
    {
        return $this->textField;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/honeypot.mustache';
    }
}
