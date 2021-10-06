<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\Callback;
use eLife\Journal\Helper\Humanizer;
use eLife\Journal\ViewModel\Form;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\MessageGroup;
use InvalidArgumentException;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;
use function array_filter;
use function array_values;

final class FormViewConverter implements ViewModelConverter
{
    private $patternRenderer;
    private $honeypotField;

    public function __construct(PatternRenderer $patternRenderer, string $honeypotField = null)
    {
        $this->patternRenderer = $patternRenderer;
        $this->honeypotField = $honeypotField;
    }

    /**
     * @param FormView $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $object->setRendered();

        foreach (array_reverse($object->vars['block_prefixes']) as $prefix) {
            switch ($prefix) {
                case 'choice':
                    if ($object->vars['multiple']) {
                        $options = array_map(function (ChoiceView $choice) use ($object) {
                            static $co = -1;
                            $co++;
                            return new ViewModel\CheckboxesOption($object->vars['id'].'_'.$co, $object->vars['full_name'].'[]', $choice->value, $choice->label, in_array($choice->value, $object->vars['data']));
                        }, $object->vars['choices']);

                        return new ViewModel\Checkboxes($object->vars['id'], $options, new ViewModel\FormLabel($this->getLabel($object)),
                            $object->vars['full_name'], $object->vars['required'], $object->vars['disabled'], $this->getState($object),
                            $this->getMessageGroup($object));
                    } else {
                        $options = array_map(function (ChoiceView $choice) use ($object) {
                            return new ViewModel\SelectOption($choice->value, $choice->label, $choice->value === $object->vars['value']);
                        }, $object->vars['choices']);

                        if (!empty($object->vars['placeholder'])) {
                            array_unshift($options, new ViewModel\SelectOption('', $object->vars['placeholder']));
                        }

                        return new ViewModel\Select($object->vars['id'], $options, new ViewModel\FormLabel($this->getLabel($object)),
                            $object->vars['full_name'], $object->vars['required'], $object->vars['disabled'], $this->getState($object),
                            $this->getMessageGroup($object));
                    }
                    break;
                case 'email':
                    $field = ViewModel\TextField::emailInput(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'], $object->vars['full_name'], $this->getInfoLink($object),
                        $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'],
                        $object->vars['disabled'], $this->getAutofocus($object), $object->vars['value'],
                        $this->getState($object), $this->getMessageGroup($object));

                    if ($object->vars['name'] === $this->honeypotField) {
                        return new ViewModel\Honeypot($field);
                    }

                    return $field;
                case 'form':
                    $form = new ViewModel\Form($object->vars['action'], $object->vars['full_name'], $object->vars['method']);

                    $children = array_map([$this, 'convert'], $object->children);

                    return new Form($form, $this->patternRenderer->render(...array_values($children)));
                case 'hidden':
                    return new ViewModel\HiddenField($object->vars['full_name'], $object->vars['id'], $object->vars['value']);
                case 'submit':
                    return ViewModel\Button::form($this->getLabel($object), ViewModel\Button::TYPE_SUBMIT, $object->vars['full_name'], ViewModel\Button::SIZE_MEDIUM,
                        ViewModel\Button::STYLE_DEFAULT, $object->vars['id'], true, false
                    );
                case 'text':
                    return ViewModel\TextField::textInput(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'], $object->vars['full_name'], $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'], $object->vars['disabled'], $this->getAutofocus($object), $object->vars['value'],
                        $this->getState($object), $this->getMessageGroup($object));
                case 'textarea':
                    return new ViewModel\TextArea(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'],
                        $object->vars['full_name'],
                        $object->vars['value'],
                        $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'],
                        $object->vars['disabled'],
                        $this->getAutofocus($object),
                        null,
                        10,
                        null,
                        $this->getState($object),
                        $this->getMessageGroup($object)
                    );
            }
        }

        throw new InvalidArgumentException('Unknown form type: '.implode(', ', $object->vars['block_prefixes']));
    }

    /**
     * @param string|null
     */
    private function getState(FormView $form)
    {
        if (false === $form->vars['submitted']) {
            return null;
        }

        return count($form->vars['errors']) ? ViewModel\TextField::STATE_INVALID : ViewModel\TextField::STATE_VALID;
    }

    /**
     * @param MessageGroup|null
     */
    private function getMessageGroup(FormView $form)
    {
        if (0 === count($form->vars['errors'])) {
            return null;
        }

        $errors = array_map(Callback::method('getMessage'), iterator_to_array($form->vars['errors']));

        return MessageGroup::forErrorText(implode(' ', $errors));
    }

    private function getAutofocus(FormView $form) : bool
    {
        return $object->vars['attr']['autofocus'] ?? count($form->vars['errors']) > 0 ?? false;
    }

    private function getLabel(FormView $form) : string
    {
        return $form->vars['label'] ?? Humanizer::humanize($form->vars['name']);
    }

    /**
     * @return ViewModel\FormFieldInfoLink|null
     */
    private function getInfoLink(FormView $form)
    {
        if (empty($form->vars['info_link'])) {
            return null;
        }

        return new ViewModel\FormFieldInfoLink($form->vars['info_link']['name'], $form->vars['info_link']['url']);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof FormView;
    }
}
