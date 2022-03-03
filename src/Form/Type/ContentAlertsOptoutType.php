<?php

namespace eLife\Journal\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

final class ContentAlertsOptoutType extends AbstractType
{
    use ReasonsForLeaving;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addReasonsForLeaving($builder)
            ->add('contact_id', HiddenType::class)
            ->add('optout', SubmitType::class, ['label' => 'Opt-out']);
    }

    public static function addContactId(FormInterface $form, int $contactId) : FormInterface
    {
        $form->add('contact_id', HiddenType::class, ['data' => $contactId]);

        return $form;
    }
}
