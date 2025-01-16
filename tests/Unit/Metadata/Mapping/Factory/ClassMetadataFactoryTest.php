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
        public readonly mixed $a1,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'A';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForFactoryTestA2 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $a2,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'A';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AttrForFactoryTestA3 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $a1 = null,
        public readonly mixed $a2 = null,
        public readonly mixed $a3 = null,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'A';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForFactoryTestB1 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $b1,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'B';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForFactoryTestB2 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $b2,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'B';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class AttrForFactoryTestB3 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $b1 = null,
        public readonly mixed $b2 = null,
        public readonly mixed $b3 = null,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'B';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForFactoryTestC1 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $c1,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'C';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForFactoryTestC2 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $c2,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'C';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AttrForFactoryTestC3 extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $c1 = null,
        public readonly mixed $c2 = null,
        public readonly mixed $c3 = null,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'C';
    }

    public function getGroups(): array
    {
        return $this->groups;
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

#[AttrForFactoryTestA3(a1: 1, a2: 2, groups: [MetadataAttribute::DEFAULT_GROUP, 'group_1'])]
#[AttrForFactoryTestA3(a1: 1, groups: ['group_2'])]
#[AttrForFactoryTestA3(a1: 4, a3: 3)]
class RepeatableExample
{
    #[AttrForFactoryTestB3(b1: 1)]
    #[AttrForFactoryTestB3(b1: 2, b2: 2, b3: 3, groups: [MetadataAttribute::DEFAULT_GROUP, 'group_1'])]
    #[AttrForFactoryTestB3(b3: 3, groups: ['group_1'])]
    public $x;

    #[AttrForFactoryTestC3(c1: 1)]
    #[AttrForFactoryTestC3(c1: 2, c2: 2, c3: 3, groups: [MetadataAttribute::DEFAULT_GROUP, 'group_1'])]
    #[AttrForFactoryTestC3(c3: 3, groups: ['group_1'])]
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
                    \Attribute::TARGET_CLASS => [AttrForFactoryTestA1::class, AttrForFactoryTestA2::class, AttrForFactoryTestA3::class],
                    \Attribute::TARGET_PROPERTY => [AttrForFactoryTestB1::class, AttrForFactoryTestB2::class, AttrForFactoryTestB3::class],
                    \Attribute::TARGET_METHOD => [AttrForFactoryTestC1::class, AttrForFactoryTestC2::class, AttrForFactoryTestC3::class],
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

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $classMetadata->getPropertiesMetadataByGroups()['y']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c2'] ?? null);
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

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $metadata->getPropertiesMetadataByGroups()['y']);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['B.b1'] ?? null);
        $this->assertSame(null, $metadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['B.b2'] ?? null);
        $this->assertSame(3, $metadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c1'] ?? null);
        $this->assertSame(6, $metadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c2'] ?? null);
    }

    public function testLoadMetadataForRepeatableClass()
    {
        $factory = new ClassMetadataFactory($this->loader);
        $classMetadata = $factory->getMetadataFor(RepeatableExample::class);

        $this->assertInstanceOf(ClassMetadataForFactoryTest::class, $classMetadata);
        $this->assertSame(4, $classMetadata->getDataByGroups()['A.a1']);
        $this->assertSame(null, $classMetadata->getDataByGroups()['A.a2']);
        $this->assertSame(3, $classMetadata->getDataByGroups()['A.a3']);

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $classMetadata->getPropertiesMetadataByGroups()['x']);
        $this->assertSame(2, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b1']);
        $this->assertSame(2, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b2']);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups()['B.b3']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups(['group_1'])['B.b1']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups(['group_1'])['B.b2']);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['x']->getDataByGroups(['group_1'])['B.b3']);

        $this->assertInstanceOf(AttrMetadataForFactoryTest::class, $classMetadata->getPropertiesMetadataByGroups()['y']);
        $this->assertSame(2, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c1']);
        $this->assertSame(2, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c2']);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups()['C.c3']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups(['group_1'])['C.c1']);
        $this->assertSame(null, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups(['group_1'])['C.c2']);
        $this->assertSame(3, $classMetadata->getPropertiesMetadataByGroups()['y']->getDataByGroups(['group_1'])['C.c3']);
    }
}
