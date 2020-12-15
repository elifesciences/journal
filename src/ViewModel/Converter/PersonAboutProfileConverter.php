<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Person;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\CanConvert;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class PersonAboutProfileConverter implements ViewModelConverter
{
    use CanConvert;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Person $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $extra = array_filter([
            'Expertise' => $object->getResearch() ? $object->getResearch()->getExpertises()->map(Callback::method('getName'))->toArray() : [],
            'Research focus' => $object->getResearch() ? $object->getResearch()->getFocuses() : [],
            'Experimental organism' => $object->getResearch() ? $object->getResearch()->getOrganisms() : [],
            'Competing interests statement' => $object->getCompetingInterests(),
        ]);

        $extra = $extra ? new ViewModel\DefinitionList($extra, true) : null;

        if ($object->getAffiliations()->notEmpty()) {
            $role = implode('<br>', $object->getAffiliations()->map(Callback::method('toString'))->toArray());
        } else {
            $role = $object->getTypeLabel();
        }

        if ($context['compact'] ?? false) {
            return new ViewModel\AboutProfile(
                $object->getDetails()->getPreferredName(),
                $role,
                null,
                $extra ? $this->patternRenderer->render($extra) : null
            );
        }

        if ($object->getThumbnail()) {
            $image = $this->viewModelConverter->convert($object->getThumbnail(), null, ['width' => 412, 'height' => 232]);
        } else {
            $image = null;
        }

        $profile = $object->getProfile()->map($this->willConvertTo());

        if ($extra) {
            $profile = $profile->append($extra);
        }

        return new ViewModel\AboutProfile(
            $object->getDetails()->getPreferredName(),
            $role,
            $image,
            $this->patternRenderer->render(...$profile)
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Person && ViewModel\AboutProfile::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
