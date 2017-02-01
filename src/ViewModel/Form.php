<?php

namespace eLife\Journal\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Form as BaseForm;

final class Form implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use SimplifyAssets;

    private $action;
    private $id;
    private $method;
    private $form;

    public function __construct(BaseForm $form, string $contents)
    {
        Assertion::notBlank($form);

        $this->action = $form['action'];
        $this->id = $form['id'];
        $this->method = $form['method'];
        $this->form = $contents;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/form.mustache';
    }
}
