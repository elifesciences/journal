<?php

namespace test\eLife\Journal\Security;

use eLife\Journal\Security\XpubTokenGenerator;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\Attribute\TimeSensitive;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class XpubTokenGeneratorTest extends TestCase
{
    #[Test]
    #[TimeSensitive(XpubTokenGenerator::class)]
    public function it_generates_a_token()
    {
        $now = strtotime('-1 second');

        ClockMock::withClockMock($now);

        $tokenGenerator = new XpubTokenGenerator('client_id', 'client_secret');

        $token = $tokenGenerator->generate(new InMemoryUser('username', 'password'), true);

        $generated = (array) JWT::decode($token, 'client_secret', ['HS256']);

        $this->assertCount(5, $generated);
        $this->assertSame('client_id', $generated['iss']);
        $this->assertSame($now, $generated['iat']);
        $this->assertSame($now + 60, $generated['exp']);
        $this->assertSame('username', $generated['id']);
        $this->assertTrue($generated['new-session']);
    }
}
