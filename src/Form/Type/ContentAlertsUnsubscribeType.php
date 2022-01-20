<?php

namespace eLife\Journal\Form\Type;

use eLife\Journal\Etoc\EarlyCareer;
use eLife\Journal\Etoc\ElifeNewsletter;
use eLife\Journal\Etoc\LatestArticles;
use eLife\Journal\Etoc\Technology;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContentAlertsUnsubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add('unsubscribe', SubmitType::class);
    }
}
