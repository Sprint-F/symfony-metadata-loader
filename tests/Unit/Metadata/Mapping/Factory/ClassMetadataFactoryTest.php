<?php

namespace SprintF\Tests\Unit\Metadata\Mapping\Factory;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\Factory\ClassMetadataFactory;
use SprintF\Metadata\Mapping\Loader\AttributeLoader;
use SprintF\Metadata\Mapping\PropertyMetadata;
use SprintF\Tests\Support\UnitTester;

// ---------------------------------------------------------------------------------------------------------------------
// ---- Attributes -----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForFactoryTestA1 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $a1,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'A';
    }
}
#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForFactoryTestA2 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $a2,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'A';
    }
}
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForFactoryTestB1 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $b1,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'B';
    }
}
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForFactoryTestB2 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $b2,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'B';
    }
}
#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForFactoryTestC1 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $c1,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'C';
    }
}
#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForFactoryTestC2 extends MetadataAttribute
{
    public function __construct(
        readonly public mixed $c2,
        ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
        parent::__construct($groups);
    }

    public function getKey(): string
    {
        return 'C';
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Metadata -------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class ClassMetadataForFactoryTest extends ClassMetadata
{
}

class AttrMetadataForFactoryTest extends PropertyMetadata
{
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Classes for extract Metadata -----------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

#[AttrForFactoryTestA1(a1: 1)]
class SimpleExample
{
    #[AttrForFactoryTestB1(b1: 2)]
    public $x;

    #[AttrForFactoryTestC1(c1: 3)]
    public function getY()
    {
    }
}

#[AttrForFactoryTestA2(a2: 4)]
class ExtendedExample extends SimpleExample
{
    #[AttrForFactoryTestB2(b2: 5)]
    public $x;

    #[AttrForFactoryTestC2(c2: 6)]
    public function getY()
    {
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Tests ----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class ClassMetadataFactoryTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected AttributeLoader $loader;

    protected function _before()
    {
        $this->loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_CLASS => [AttrForFactoryTestA1::class, AttrForFactoryTestA2::class],
                    \Attribute::TARGET_PROPERTY => [AttrForFactoryTestB1::class, AttrForFactoryTestB2::class],
                    \Attribute::TARGET_METHOD => [AttrForFactoryTestC1::class, AttrForFactoryTestC2::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadataForFactoryTest::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return AttrMetadataForFactoryTest::class;
            }
        };
    }

    public function testLoadMetadataForSimpleClass()
    {
        $factory = new ClassMetadataFactory($this->loader);
        $classMetadata = $factory->getMetadataFor(SimpleExample::class);

        $this->assertInstanceOf(ClassMetadataForFactoryTest::class, $classMetadata);
        $this->assertSame(1, $classMetadata->getDataByGroups()['A.a1'] ?? null);
        $this->assertSame(null, $classMetadata->getDataByGroups()['A.a2'] ?? null);

        $this->assertCount(2, $classMetadata->getPropertiesMetadataByGroups());

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $classMetadata->getPropertiesMetadataByGroups()['x']);
        $this->assertSame(2, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['C.c2'] ?? null);

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $classMetadata->getPropertiesMetadataByGroups()['getY']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['C.c2'] ?? null);
    }

    public function testLoadMetadataForExtendedClass()
    {
        $factory = new ClassMetadataFactory($this->loader);
        $metadata = $factory->getMetadataFor(ExtendedExample::class);

        $this->assertInstanceOf(ClassMetadataForFactoryTest::class, $metadata);
        $this->assertSame(1, $metadata->getDataByGroups()['A.a1'] ?? null);
        $this->assertSame(4, $metadata->getDataByGroups()['A.a2'] ?? null);

        $this->assertCount(2, $metadata->getPropertiesMetadataByGroups());

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $metadata->getPropertiesMetadataByGroups()['x']);
        $this->assertSame(2, $metadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(5, $metadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['C.c2'] ?? null);

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $metadata->getPropertiesMetadataByGroups()['getY']);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(3, $metadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(6, $metadata->getPropertiesMetadataByGroups()['getY']->getDataByGroups()['C.c2'] ?? null);
    }
}
