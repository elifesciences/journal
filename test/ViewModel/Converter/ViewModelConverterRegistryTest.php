<?php

namespace test\eLife\Journal\ViewModel\Converter;

use DateTimeImmutable;
use PHPUnit_Framework_TestCase;
use stdClass;
use eLife\Journal\ViewModel\Converter\ViewModelConverterRegistry;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;

final class ViewModelConverterRegistryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_supports_the_union_of_its_converters_supports()
    {
        $registry = new ViewModelConverterRegistry();
        $registry->add($this->aConverterThatSupports(stdClass::class, 'AViewModel'));
        $registry->add($this->aConverterThatSupports(DateTimeImmutable::class, 'BViewModel'));
        $this->assertSupports($registry, new stdClass(), 'AViewModel');
        $this->assertSupports($registry, new DateTimeImmutable(), 'BViewModel');
        $this->assertNotSupports($registry, new stdClass(), 'BViewModel');
        $this->assertNotSupports($registry, new DateTimeImmutable(), 'AViewModel');
        $this->assertNotSupports($registry, new stdClass(), 'UnknownViewModel');
    }

    private function aConverterThatSupports($objectClass, $viewModelClass)
    {
        $stub = $this->createMock(ViewModelConverter::class);
        $stub
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnCallback(function($object, $specifiedViewModelClass) use ($objectClass, $viewModelClass) {
                return $object instanceof $objectClass && $viewModelClass == $specifiedViewModelClass;
            }));
        return $stub;
    }

    private function assertSupports(ViewModelConverterRegistry $registry, $object, string $viewModelClass)
    {
        $this->assertTrue(
            $registry->supports($object, $viewModelClass),
            "Registry should support " . get_class($object) . " turning into $viewModelClass, but it doesn't"
        );
    }

    /**
     * Probably grammatically incorrect, but that's the naming convention for negative assertions in PHPUnit
     */
    private function assertNotSupports(ViewModelConverterRegistry $registry, $object, string $viewModelClass)
    {
        $this->assertFalse(
            $registry->supports($object, $viewModelClass),
            "Registry should not support " . get_class($object) . " turning into $viewModelClass, but it does"
        );
    }
}
