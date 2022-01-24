<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

final class ContentAlertsUnsubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dump($options);

        $choices = [
            "I'm no longer interested",
            'They are not relevant to me',
            'I get too many emails',
            'I never wanted to receive these emails',
            'Other',
        ];

        $builder
            ->add('contact_id', HiddenType::class)
            ->add('groups', HiddenType::class)
            ->add('reason', ChoiceType::class,
                [
                    'label' => 'Help us improve by telling us why you want to unsubscribe (optional).',
                    'choices' => array_combine($choices, $choices),
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add('reason_other', TextareaType::class, [
                'label' => 'If other',
            ])
            ->add('unsubscribe', SubmitType::class);
    }

    public static function addContactId(FormInterface $form, int $contactId)
    {
        $form
            ->add('contact_id', HiddenType::class, ['data' => $contactId]);

        return $form;
    }
}
