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
        $builder
            ->add('contact_id', HiddenType::class)
            ->add('groups', HiddenType::class)
            ->add('unsubscribe', SubmitType::class);
    }

    public static function addContactId(FormInterface $form, int $contactId) : FormInterface
    {
        $form->add('contact_id', HiddenType::class, ['data' => $contactId]);

        return $form;
    }
}
