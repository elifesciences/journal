<?php

namespace eLife\Journal\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InfoLinkExtension extends AbstractTypeExtension
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['info_link'] = ['name' => 'Privacy notice', 'url' => $this->urlGenerator->generate('privacy')];
    }

    public function getExtendedType() : string
    {
        return EmailType::class;
    }
}
