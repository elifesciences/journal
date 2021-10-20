<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Security\HypothesisTokenGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class HypothesisTokenExtension extends AbstractExtension
{
    private $tokenGenerator;

    public function __construct(HypothesisTokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
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
