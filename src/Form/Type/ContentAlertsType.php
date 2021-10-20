<?php

namespace eLife\Journal\Form\Type;

use eLife\Journal\Guzzle\CiviCrmClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                    'data' => [
                        CiviCrmClient::LABEL_LATEST_ARTICLES,
                    ],
                ]
            )
            ->add('subscribe', SubmitType::class);
    }
}