<?php

namespace eLife\Journal\Security;

use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\User\UserInterface;

final class XpubTokenGenerator
{
    const TOKEN_TTL = 60;

    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function generate(UserInterface $user) : string
    {
        return JWT::encode([
            'iat' => time(),
            'exp' => time() + self::TOKEN_TTL,
            'id' => $user->getUsername(),
        ], $this->secret);
    }
}
