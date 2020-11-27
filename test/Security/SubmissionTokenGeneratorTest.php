<?php

namespace test\eLife\Journal\Security;

use eLife\Journal\Security\SubmissionTokenGenerator;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Security\Core\User\User;

final class SubmissionTokenGeneratorTest extends TestCase
{
    /**
     * @test
     * @group time-sensitive
     */
    public function it_generates_a_token()
    {
        $now = strtotime('-1 second');

        ClockMock::withClockMock($now);

        $tokenGenerator = new SubmissionTokenGenerator('client_id', 'client_secret');

        $token = $tokenGenerator->generate(new User('username', 'password'), true);

        $generated = (array) JWT::decode($token, 'client_secret', ['HS256']);

        $this->assertCount(5, $generated);
        $this->assertSame('client_id', $generated['iss']);
        $this->assertSame($now, $generated['iat']);
        $this->assertSame($now + 60, $generated['exp']);
        $this->assertSame('username', $generated['id']);
        $this->assertTrue($generated['new-session']);
    }
}
