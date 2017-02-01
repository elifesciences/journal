<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank(['message' => 'Please provide your name'])],
                    'attr' => [
                        'autofocus' => true,
                    ],
                ]
            )
            ->add('email', EmailType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please provide your email address.']),
                        new Email(['message' => 'Please provide a valid email address.']),
                    ],
                ]
            )
            ->add('question', TextareaType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Please let us know your question.']),
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id' => self::class,
        ]);
    }
}
