<?php

namespace eLife\Journal\Form\Type;

use eLife\Journal\Guzzle\CiviCrmClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                    'disabled' => !empty($options['data']['contact_id']),
                    'attr' => [
                        'autofocus' => empty($options['data']['contact_id']),
                    ],
                ]
            );

        if (!empty($options['data']['contact_id'])) {
            $builder
                ->add('contact_id', HiddenType::class)
                ->add('groups', HiddenType::class)
                ->add('first_name', TextType::class, [
                    'attr' => [
                        'autofocus' => true,
                    ],
                ])
                ->add('last_name', TextType::class);
        }

        $builder
            ->add('preferences', ChoiceType::class,
                [
                    'label' => 'I would like to receive the following regular emails from eLife:',
                    'choices' => [
                        [
                            'The latest scientific articles published by eLife (twice weekly)' => CiviCrmClient::LABEL_LATEST_ARTICLES,
                        ],
                        [
                            'Our other newsletters' => [
                                'Early-career researchers newsletter (monthly)' => CiviCrmClient::LABEL_EARLY_CAREER,
                                'Technology and Innovation newsletter (every two months)' => CiviCrmClient::LABEL_TECHNOLOGY,
                                'eLife newsletter (every two months)' => CiviCrmClient::LABEL_ELIFE_NEWSLETTER,
                            ],
                        ],
                    ],
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please select an email type to subscribe.']),
                    ],
                ]
            )
            ->add('subscribe', SubmitType::class);
    }
}
