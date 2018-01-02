<?php

namespace test\eLife\Journal\Security\Voter;

use eLife\Journal\Security\Voter\SessionAttributeVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Traversable;

final class SessionAttributeVoterTest extends TestCase
{
    /**
     * @test
     * @dataProvider noSetValueProvider
     */
    public function it_votes_without_a_set_value($subject, array $roles, array $attributes, int $expected)
    {
        $requestStack = new RequestStack();
        $requestStack->push($request = new Request());
        $request->setSession($session = new Session(new MockArraySessionStorage()));
        foreach ($attributes as $key => $value) {
            $session->set($key, $value);
        }

        $voter = new SessionAttributeVoter($requestStack, 'foo', 'attribute');

        $this->assertSame($expected, $voter->vote(new AnonymousToken('secret', 'anon.'), $subject, $roles));
    }

    public function noSetValueProvider() : Traversable
    {
        yield 'no roles' => [null, [], [], VoterInterface::ACCESS_ABSTAIN];
        yield 'some other role' => [null, ['bar'], [], VoterInterface::ACCESS_ABSTAIN];
        yield 'with value' => [null, ['foo'], ['attribute' => true], VoterInterface::ACCESS_GRANTED];
        yield 'with null value' => [null, ['foo'], ['attribute' => null], VoterInterface::ACCESS_GRANTED];
        yield 'without attribute' => [null, ['foo'], [], VoterInterface::ACCESS_DENIED];
    }

    /**
     * @test
     * @dataProvider setValueProvider
     */
    public function it_votes_with_a_set_value($requiredValue, $subject, array $roles, array $attributes, int $expected)
    {
        $requestStack = new RequestStack();
        $requestStack->push($request = new Request());
        $request->setSession($session = new Session(new MockArraySessionStorage()));
        foreach ($attributes as $key => $value) {
            $session->set($key, $value);
        }

        $voter = new SessionAttributeVoter($requestStack, 'foo', 'attribute', $requiredValue);

        $this->assertSame($expected, $voter->vote(new AnonymousToken('secret', 'anon.'), $subject, $roles));
    }

    public function setValueProvider() : Traversable
    {
        yield 'no roles' => ['value', null, [], [], VoterInterface::ACCESS_ABSTAIN];
        yield 'some other role' => ['value', null, ['bar'], [], VoterInterface::ACCESS_ABSTAIN];
        yield 'with boolean value' => ['value', null, ['foo'], ['attribute' => true], VoterInterface::ACCESS_DENIED];
        yield 'with string value' => ['value', null, ['foo'], ['attribute' => 'value'], VoterInterface::ACCESS_GRANTED];
        yield 'without attribute' => ['value', null, ['foo'], [], VoterInterface::ACCESS_DENIED];
    }
}
