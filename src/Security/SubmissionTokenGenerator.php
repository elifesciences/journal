<?php

namespace eLife\Journal\Security;

use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\User\UserInterface;

final class SubmissionTokenGenerator
{
    const TOKEN_TTL = 60;

    private $clientId;
    private $clientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function generate(UserInterface $user, bool $newSession = false) : string
    {
        return JWT::encode([
            'iss' => $this->clientId,
            'iat' => time(),
            'exp' => time() + self::TOKEN_TTL,
            'id' => $user->getUsername(),
            'new-session' => $newSession,
        ], $this->clientSecret);
    }
}
