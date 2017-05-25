<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Person;
use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\CanConvert;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\DefinitionList;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class PersonAboutProfileConverter implements ViewModelConverter
{
    use CanConvert;
    use CreatesIiifUri;

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

        if ($extra) {
            $extra = $this->patternRenderer->render(new DefinitionList($extra));
        } else {
            $extra = null;
        }

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
                $extra
            );
        }

        if ($object->getThumbnail()) {
            $srcset = [];
            if ($object->getThumbnail()->getWidth() >= 500) {
                $srcset[500] = $this->iiifUri($object->getThumbnail(), 500, 282);
            }

            $image = new ViewModel\Picture(
                [],
                new ViewModel\Image(
                    $this->iiifUri($object->getThumbnail(), 250, 141),
                    $srcset,
                    $object->getThumbnail()->getAltText()
                )
            );
        } else {
            $image = null;
        }

        $profile = $object->getProfile()->map($this->willConvertTo());

        if ($extra) {
            $profile = $profile->append();
        }

        $extra = array_filter([
            'Expertise' => $object->getResearch() ? $object->getResearch()->getExpertises()->map(Callback::method('getName'))->toArray() : [],
            'Research focus' => $object->getResearch() ? $object->getResearch()->getFocuses() : [],
            'Experimental organism' => $object->getResearch() ? $object->getResearch()->getOrganisms() : [],
            'Competing interests statement' => $object->getCompetingInterests(),
        ]);

        if ($extra) {
            $profile = $profile->append(new DefinitionList($extra));
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
