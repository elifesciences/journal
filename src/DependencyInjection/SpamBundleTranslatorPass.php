<?php

namespace eLife\Journal\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * isometriks/spam-bundle v1.0.7+ type-hints Symfony\Contracts\Translation\TranslatorInterface,
 * which Symfony 3.4's IdentityTranslator does not implement. Null out the translator argument
 * so the form extensions work without translation until Symfony is upgraded to 4.4+.
 */
final class SpamBundleTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // timed_spam: __construct($provider, $translator, $translationDomain, $defaults)
        if ($container->hasDefinition('isometriks_spam.form.extension.type.timed_spam')) {
            $container->getDefinition('isometriks_spam.form.extension.type.timed_spam')->replaceArgument(1, null);
        }
        // honeypot: __construct($translator, $translationDomain, $defaults)
        if ($container->hasDefinition('isometriks_spam.form.extension.type.honeypot')) {
            $container->getDefinition('isometriks_spam.form.extension.type.honeypot')->replaceArgument(0, null);
        }
    }
}
