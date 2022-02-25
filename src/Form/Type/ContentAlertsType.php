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

final class ContentAlertsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['data']['contact_id'])) {
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
        } else {
            $builder
                ->add('email', HiddenType::class)
                ->add('contact_id', HiddenType::class)
                ->add('groups', HiddenType::class)
                ->add('first_name', TextType::class, [
                    'label' => 'First name (optional)',
                    'attr' => [
                        'autofocus' => true,
                    ],
                ])
                ->add('last_name', TextType::class, [
                    'label' => 'Last name (optional)',
                ]);
        }

        $builder
            ->add('preferences', ChoiceType::class,
                [
                    'label' => 'I would like to receive the following regular emails from eLife:',
                    'choices' => $this->preferencesVariant($options['data']['variant'] ?? null),
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please select an email type to subscribe.']),
                    ],
                ]
            )
            ->add(empty($options['data']['contact_id']) ? 'subscribe' : 'update', SubmitType::class);
    }

    private function preferencesVariant(string $variant = null) : array
    {
        $preferences = [
            'default' => ['The latest scientific articles published by eLife (twice weekly)' => LatestArticles::LABEL],
            'early-career' => ['Early-career researchers newsletter (monthly)' => EarlyCareer::LABEL],
            'technology' => ['Technology and Innovation newsletter (every two months)' => Technology::LABEL],
            'elife-newsletter' => ['eLife newsletter (every two months)' => ElifeNewsletter::LABEL],
        ];
        $main = [];
        $other = [];

        foreach ($preferences as $k => $preference) {
            if (($variant ?? 'default') === $k) {
                $main += $preference;
            } else {
                $other += $preference;
            }
        }

        return [
            $main,
            ['Our other newsletters' => $other],
        ];
    }
}
