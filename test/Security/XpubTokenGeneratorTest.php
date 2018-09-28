<?php

namespace test\eLife\Journal\Security;

use eLife\Journal\Security\XpubTokenGenerator;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Security\Core\User\User;

final class XpubTokenGeneratorTest extends TestCase
{
    /**
     * @test
     * @group time-sensitive
     */
    public function it_generates_a_token()
    {
        $now = strtotime('-1 second');

        ClockMock::withClockMock($now);

        $tokenGenerator = new XpubTokenGenerator('key');

        $token = $tokenGenerator->generate(new User('username', 'password'));

        $generated = (array) JWT::decode($token, 'key', ['HS256']);

        $this->assertCount(3, $generated);
        $this->assertSame($now, $generated['iat']);
        $this->assertSame($now + 60, $generated['exp']);
        $this->assertSame('username', $generated['id']);
    }
}
