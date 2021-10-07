<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContentAlertsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please provide your email address.']),
                        new Email(['message' => 'Please provide a valid email address.']),
                    ],
                ]
            )
            ->add('first_name', TextType::class,
                [
                    'label' => 'First name (optional)',
                    'attr' => [
                        'autofocus' => true,
                    ],
                ]
            )
            ->add('last_name', TextType::class,
                [
                    'label' => 'Last name (optional)',
                    'attr' => [
                        'autofocus' => true,
                    ],
                ]
            )
            ->add('preferences', ChoiceType::class,
                [
                    'label' => 'I would like to receive the following regular emails from eLife:',
//                    'choices' => [
//                        'The latest scientific articles published by eLife (twice weekly)' => 'latest_articles',
//                        'Early-career researchers newsletter (monthly)' => 'early_career',
//                        'Technology and Innovation newsletter (every two months)' => 'technology',
//                        'eLife newsletter (every two months)' => 'elife_newsletter',
//                    ],
                    'choices' => [
                        [
                            'The latest scientific articles published by eLife (twice weekly)' => 'latest_articles',
                        ],
                        [
                            'Our other newsletters' => [
                                'Early-career researchers newsletter (monthly)' => 'early_career',
                                'Technology and Innovation newsletter (every two months)' => 'technology',
                                'eLife newsletter (every two months)' => 'elife_newsletter',
                            ],
                        ],
                    ],
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please select an email type to subscribe.']),
                    ],
                    'data' => ['latest_articles'],
                ]
            )
            ->add('subscribe', SubmitType::class);
    }
}
