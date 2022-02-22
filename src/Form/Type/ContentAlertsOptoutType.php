<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

final class ContentAlertsOptoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact_id', HiddenType::class)
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
                    'attr' => [
                        'isHiddenUntilChecked' => true,
                        'checkboxId' => 'content_alerts_optout_reasons_5',
                    ],
                ]
            )
            ->add('opt-out', SubmitType::class);
    }

    public static function addContactId(FormInterface $form, int $contactId) : FormInterface
    {
        $form->add('contact_id', HiddenType::class, ['data' => $contactId]);

        return $form;
    }
}
