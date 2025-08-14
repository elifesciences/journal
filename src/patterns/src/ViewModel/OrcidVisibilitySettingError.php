<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class OrcidVisibilitySettingError implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $contactUri;

    public function __construct(string $contactUri)
    {
        $this->contactUri = $contactUri;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/orcid-error-visibility-setting.mustache';
    }
}
