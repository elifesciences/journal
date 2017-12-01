<?php

namespace test\eLife\Journal\Security;

use eLife\Journal\Security\HypothesisTokenGenerator;
use Firebase\JWT\JWT;
use PHPUnit_Framework_TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Security\Core\User\User;

final class HypothesisTokenGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group time-sensitive
     */
    public function it_generates_a_token()
    {
        $now = strtotime('-1 second');

        ClockMock::withClockMock($now);

        $tokenGenerator = new HypothesisTokenGenerator('authority', 'client_id', 'client_secret');

        $token = $tokenGenerator->generate(new User('username', 'password'));

        $generated = (array) JWT::decode($token, 'client_secret', ['HS256']);

        $this->assertCount(5, $generated);
        $this->assertSame('hypothes.is', $generated['aud']);
        $this->assertSame('client_id', $generated['iss']);
        $this->assertSame('acct:username@authority', $generated['sub']);
        $this->assertSame($now, $generated['nbf']);
        $this->assertSame($now + 600, $generated['exp']);
    }
}
