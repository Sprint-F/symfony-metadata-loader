<?php

namespace SprintF\Tests\Unit\Metadata\Mapping\Loader;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\Loader\AttributeLoader;
use SprintF\Metadata\Mapping\PropertyMetadata;
use SprintF\Tests\Support\UnitTester;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrClassA extends MetadataAttribute
{
    public function __construct(
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
class AttrClassB extends MetadataAttribute
{
    public function __construct(
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

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrClassC extends AttrClassB
{
    public function getKey(): string
    {
        return 'C';
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrPropertyA extends MetadataAttribute
{
    public function __construct(
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
class AttrPropertyB extends MetadataAttribute
{
    public function __construct(
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
class AttrPropertyC extends AttrPropertyB
{
    public function getKey(): string
    {
        return 'C';
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrMethodA extends MetadataAttribute
{
    public function __construct(
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

#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrMethodB extends MetadataAttribute
{
    public function __construct(
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
class AttrMethodC extends AttrMethodB
{
    public function getKey(): string
    {
        return 'C';
    }
}

class AttributeLoaderPrivateTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testIsKnownAttributeDirect()
    {
        $loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_CLASS => [AttrClassA::class],
                    \Attribute::TARGET_PROPERTY => [AttrPropertyA::class],
                    \Attribute::TARGET_METHOD => [AttrMethodA::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadata::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropertyMetadata::class;
            }
        };

        $reflector = new \ReflectionMethod($loader, 'isKnownAttribute');
        $isKnownAttribute = $reflector->getClosure($loader);

        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_CLASS, AttrClassA::class));
        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_CLASS, AttrClassB::class));

        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_PROPERTY, AttrPropertyA::class));
        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_PROPERTY, AttrPropertyB::class));

        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_METHOD, AttrMethodA::class));
        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_METHOD, AttrMethodB::class));
    }

    public function testIsKnownAttributeInheritance()
    {
        $loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_CLASS => [AttrClassB::class],
                    \Attribute::TARGET_PROPERTY => [AttrPropertyB::class],
                    \Attribute::TARGET_METHOD => [AttrMethodB::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadata::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropertyMetadata::class;
            }
        };

        $reflector = new \ReflectionMethod($loader, 'isKnownAttribute');
        $isKnownAttribute = $reflector->getClosure($loader);

        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_CLASS, AttrClassA::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_CLASS, AttrClassB::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_CLASS, AttrClassC::class));

        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_PROPERTY, AttrPropertyA::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_PROPERTY, AttrPropertyB::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_PROPERTY, AttrPropertyC::class));

        $this->assertFalse($isKnownAttribute(\Attribute::TARGET_METHOD, AttrMethodA::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_METHOD, AttrMethodB::class));
        $this->assertTrue($isKnownAttribute(\Attribute::TARGET_METHOD, AttrMethodC::class));
    }

    public function testLoadAttributesByClass()
    {
        $testClass = new #[AttrClassA] #[AttrClassC] class {};

        $loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_CLASS => [AttrClassA::class, AttrClassB::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadata::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropertyMetadata::class;
            }
        };

        $reflector = new \ReflectionMethod($loader, 'loadAttributes');
        $loadAttributes = $reflector->getClosure($loader);

        $attrs = iterator_to_array($loadAttributes(new \ReflectionClass(get_class($testClass))));

        $this->assertCount(2, $attrs);
        $this->assertInstanceOf(AttrClassA::class, $attrs[0]);
        $this->assertInstanceOf(AttrClassC::class, $attrs[1]);
    }

    public function testLoadAttributesByProperty()
    {
        $testClass = new class {
            #[AttrPropertyA] #[AttrPropertyC] public $x;
        };

        $loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_PROPERTY => [AttrPropertyA::class, AttrPropertyB::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadata::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropertyMetadata::class;
            }
        };

        $reflector = new \ReflectionMethod($loader, 'loadAttributes');
        $loadAttributes = $reflector->getClosure($loader);

        $attrs = iterator_to_array($loadAttributes(new \ReflectionProperty(get_class($testClass), 'x')));

        $this->assertCount(2, $attrs);
        $this->assertInstanceOf(AttrPropertyA::class, $attrs[0]);
        $this->assertInstanceOf(AttrPropertyC::class, $attrs[1]);
    }

    public function testLoadAttributesByMethod()
    {
        $testClass = new class {
            #[AttrMethodA] #[AttrMethodC]
            public function x()
            {
            }
        };

        $loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_METHOD => [AttrMethodA::class, AttrMethodB::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadata::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropertyMetadata::class;
            }
        };

        $reflector = new \ReflectionMethod($loader, 'loadAttributes');
        $loadAttributes = $reflector->getClosure($loader);

        $attrs = iterator_to_array($loadAttributes(new \ReflectionMethod(get_class($testClass), 'x')));

        $this->assertCount(2, $attrs);
        $this->assertInstanceOf(AttrMethodA::class, $attrs[0]);
        $this->assertInstanceOf(AttrMethodC::class, $attrs[1]);
    }
}
