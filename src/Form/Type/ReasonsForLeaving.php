<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait ReasonsForLeaving
{
    public function addReasonsForLeaving(FormBuilderInterface $builder, string $idPrefix = 'content_alerts_optout') : FormBuilderInterface
    {
        return $builder
            ->add('reasons', ChoiceType::class,
                [
                    'label' => 'Help us improve by telling us why you want to opt-out (optional).',
                    'choices' => [
                        'I\'m no longer interested' => 1,
                        'They are not relevant to me' => 2,
                        'I get too many emails' => 3,
                        'I never wanted to receive these emails' => 4,
                        'Other' => 5,
                    ],
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add('other', TextType::class,
                [
                    'label' => 'Please enter your reason',
                    'attr' => [
                        'isHiddenUntilChecked' => true,
                        'checkboxId' => $idPrefix.'_reasons_5',
                    ],
                ]
            );
    }
}
