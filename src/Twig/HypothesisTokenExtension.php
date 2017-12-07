<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Security\HypothesisTokenGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig_Extension;
use Twig_Function;

final class HypothesisTokenExtension extends Twig_Extension
{
    private $tokenGenerator;

    public function __construct(HypothesisTokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function getFunctions()
    {
        return [
            new Twig_Function(
                'hypothesis_token',
                [$this, 'generateToken'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function generateToken(UserInterface $user) : string
    {
        return $this->tokenGenerator->generate($user);
    }
}
