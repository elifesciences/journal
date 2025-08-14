<?php

namespace eLife\Patterns;

interface ViewModel extends CastsToArray
{
    public function getTemplateName() : string;
}
