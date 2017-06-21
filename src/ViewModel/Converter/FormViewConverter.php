<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Journal\Helper\Humanizer;
use eLife\Journal\ViewModel\Form;
use eLife\Journal\ViewModel\HiddenInput;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use InvalidArgumentException;
use Symfony\Component\Form\FormView;

final class FormViewConverter implements ViewModelConverter
{
    private $patternRenderer;

    public function __construct(PatternRenderer $patternRenderer)
    {
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param FormView $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $object->setRendered();

        foreach (array_reverse($object->vars['block_prefixes']) as $prefix) {
            switch ($prefix) {
                case 'email':
                    return ViewModel\TextField::emailInput(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'], $object->vars['full_name'], $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'],
                        $object->vars['disabled'], $object->vars['attr']['autofocus'] ?? false, $object->vars['value'],
                        $this->getState($object));
                case 'form':
                    $form = new ViewModel\Form($object->vars['action'], $object->vars['full_name'], $object->vars['method']);

                    $children = array_map([$this, 'convert'], $object->children);

                    if ('email_cta' === $object->vars['full_name']) {
                        return new ViewModel\EmailCta(
                            'Be the first to read new articles from eLife',
                            'Sign up for alerts',
                            new ViewModel\CompactForm(
                                $form,
                                new ViewModel\Input(
                                    $children['email']['label']['labelText'],
                                    $children['email']['inputType'],
                                    $children['email']['name'],
                                    $children['email']['value'],
                                    $children['email']['placeholder']
                                ),
                                $children['submit']['text']
                            )
                        );
                    }

                    return new Form($form, $this->patternRenderer->render(...array_values($children)));
                case 'hidden':
                    return new HiddenInput($object->vars['full_name'], $object->vars['id'], $object->vars['value']);
                case 'submit':
                    return ViewModel\Button::form($this->getLabel($object), ViewModel\Button::TYPE_SUBMIT, $object->vars['full_name'], ViewModel\Button::SIZE_MEDIUM,
                        ViewModel\Button::STYLE_DEFAULT, $object->vars['id'], true, false
                    );
                case 'text':
                    return ViewModel\TextField::textInput(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'], $object->vars['full_name'], $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'], $object->vars['disabled'], $object->vars['attr']['autofocus'] ?? false, $object->vars['value'],
                        $this->getState($object));
                case 'textarea':
                    return new ViewModel\TextArea(new ViewModel\FormLabel($this->getLabel($object)),
                        $object->vars['id'],
                        $object->vars['full_name'],
                        $object->vars['value'],
                        $object->vars['attr']['placeholder'] ?? null,
                        $object->vars['required'],
                        $object->vars['disabled'],
                        $object->vars['attr']['autofocus'] ?? false,
                        null,
                        10,
                        null,
                        $this->getState($object));
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

        return count($form->vars['errors']) ? ViewModel\TextField::STATUS_ERROR : ViewModel\TextField::STATUS_VALID;
    }

    private function getLabel(FormView $form) : string
    {
        return $form->vars['label'] ?? Humanizer::humanize($form->vars['name']);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof FormView;
    }
}
