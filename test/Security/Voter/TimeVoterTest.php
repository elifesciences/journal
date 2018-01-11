<?php

namespace test\eLife\Journal\Security\Voter;

use eLife\Journal\Security\Voter\TimeVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Traversable;

final class TimeVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider voteProvider
     * @group time-sensitive
     */
    public function it_votes($subject, array $roles, int $timestamp, int $now, int $expected)
    {
        ClockMock::withClockMock($now);

        $voter = new TimeVoter('role', $timestamp);

        $this->assertSame($expected, $voter->vote(new AnonymousToken('secret', 'anon.'), $subject, $roles));
    }

    public function voteProvider() : Traversable
    {
        yield 'no roles' => [null, [], 1234, 1234, VoterInterface::ACCESS_ABSTAIN];
        yield 'some other role' => [null, ['other role'], 1234, 1234, VoterInterface::ACCESS_ABSTAIN];
        yield 'in past' => [null, ['role'], 1234, 1233, VoterInterface::ACCESS_DENIED];
        yield 'now' => [null, ['role'], 1234, 1234, VoterInterface::ACCESS_GRANTED];
        yield 'in future' => [null, ['role'], 1234, 1235, VoterInterface::ACCESS_GRANTED];
    }
}
