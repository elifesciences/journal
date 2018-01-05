<?php

namespace test\eLife\Journal\Security\Voter;

use eLife\Journal\Security\Voter\RequestHeaderVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Traversable;

final class RequestHeaderVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider voteProvider
     */
    public function it_votes($subject, array $roles, $expectedValue, $actualValue, int $expected)
    {
        $requestStack = new RequestStack();
        $requestStack->push($request = new Request());

        $request->headers->set('header', $actualValue);

        $voter = new RequestHeaderVoter($requestStack, 'role', 'header', $expectedValue, false);

        $this->assertSame($expected, $voter->vote(new AnonymousToken('secret', 'anon.'), $subject, $roles));
    }

    public function voteProvider() : Traversable
    {
        yield 'no roles' => [null, [], 'foo', 'foo', VoterInterface::ACCESS_ABSTAIN];
        yield 'some other role' => [null, ['other role'], 'foo', 'foo', VoterInterface::ACCESS_ABSTAIN];
        yield 'with value' => [null, ['role'], 'foo', 'foo', VoterInterface::ACCESS_GRANTED];
        yield 'with different value' => [null, ['role'], 'foo', 'bar', VoterInterface::ACCESS_DENIED];
        yield 'with multiple values' => [null, ['role'], ['foo', 'bar'], 'bar', VoterInterface::ACCESS_GRANTED];
        yield 'with incorrect value' => [null, ['role'], ['foo', 'bar'], 'baz', VoterInterface::ACCESS_DENIED];
    }

    /**
     * @test
     */
    public function it_can_require_a_trusted_proxy()
    {
        $requestStack = new RequestStack();
        $requestStack->push($request = new Request());

        $request->headers->set('header', 'foo');

        $voter = new RequestHeaderVoter($requestStack, 'role', 'header', 'foo', true);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote(new AnonymousToken('secret', 'anon.'), null, ['role']));
    }
}
