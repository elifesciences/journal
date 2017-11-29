<?php

namespace eLife\Journal\Security;

use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\User\UserInterface;

final class HypothesisTokenGenerator
{
    const TOKEN_TTL = 10 * 60;

    private $authority;
    private $clientId;
    private $clientSecret;

    public function __construct(string $authority, string $clientId, string $clientSecret)
    {
        $this->authority = $authority;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function generate(UserInterface $user) : string
    {
        return JWT::encode([
            'aud' => 'hypothes.is',
            'iss' => $this->clientId,
            'sub' => "acct:{$user->getUsername()}@{$this->authority}",
            'nbf' => time(),
            'exp' => time() + self::TOKEN_TTL,
        ], $this->clientSecret);
    }
}
