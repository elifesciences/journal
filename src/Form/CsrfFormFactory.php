<?php

namespace eLife\Journal\Form;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CsrfFormFactory implements FormFactoryInterface
{
    private $authorizationChecker;
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function create($type = FormType::class, $data = null, array $options = []) : FormInterface
    {
        return $this->formFactory->create($type, $data, $this->updateOptions($options));
    }

    public function createNamed($name, $type = FormType::class, $data = null, array $options = []) : FormInterface
    {
        return $this->formFactory->createNamed($name, $type, $data, $this->updateOptions($options));
    }

    public function createForProperty($class, $property, $data = null, array $options = []) : FormInterface
    {
        return $this->formFactory->createForProperty($class, $property, $data, $this->updateOptions($options));
    }

    public function createBuilder($type = FormType::class, $data = null, array $options = []) : FormBuilderInterface
    {
        return $this->formFactory->createBuilder($type, $data, $this->updateOptions($options));
    }

    public function createNamedBuilder($name, $type = FormType::class, $data = null, array $options = []) : FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, $type, $data, $this->updateOptions($options));
    }

    public function createBuilderForProperty($class, $property, $data = null, array $options = []) : FormBuilderInterface
    {
        return $this->formFactory->createBuilderForProperty($class, $property, $data, $this->updateOptions($options));
    }

    private function updateOptions(array $options) : array
    {
        $options['csrf_protection'] = $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED');

        return $options;
    }
}
