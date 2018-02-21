<?php

namespace test\eLife\Journal\Security\Voter;

use eLife\Journal\Security\Voter\FixedVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Traversable;

final class FixedVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider voteProvider
     */
    public function it_votes($subject, array $roles, bool $vote, int $expected)
    {
        $voter = new FixedVoter('role', $vote);

        $this->assertSame($expected, $voter->vote(new AnonymousToken('secret', 'anon.'), $subject, $roles));
    }

    public function voteProvider() : Traversable
    {
        yield 'no roles' => [null, [], true, VoterInterface::ACCESS_ABSTAIN];
        yield 'some other role' => [null, ['other role'], true, VoterInterface::ACCESS_ABSTAIN];
        yield 'false' => [null, ['role'], false, VoterInterface::ACCESS_DENIED];
        yield 'true' => [null, ['role'], true, VoterInterface::ACCESS_GRANTED];
    }
}
