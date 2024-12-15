<?php

namespace SprintF\Tests\Unit\Metadata\Mapping\Loader;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\Loader\AttributeLoader;
use SprintF\Metadata\Mapping\Attribute\GroupsAttribute;
use SprintF\Metadata\Mapping\PropertyMetadata;
use SprintF\Tests\Support\UnitTester;

// ---------------------------------------------------------------------------------------------------------------------
// ---- Attributes -----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForLoadTestClassX extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'X';
    }

    public function __construct(
        public $x,
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForLoadTestClassY extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'Y';
    }

    public function __construct(
        public $y,
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForLoadTestPropA extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'A';
    }

    public function __construct(
        public $a,
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForLoadTestPropB extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'B';
    }

    public function __construct(
        public $b,
    ) {
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForLoadTestMethodA extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'A';
    }

    public function __construct(
        public $a,
    ) {
    }
}
#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForLoadTestMethodB extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'B';
    }

    public function __construct(
        public $b,
    ) {
    }
}
#[\Attribute(\Attribute::TARGET_METHOD)]
class AttrForLoadTestMethodC extends MetadataAttribute
{
    public function getKey(): string
    {
        return 'C';
    }

    public function __construct(
        public $c,
    ) {
    }
}

#[\Attribute] class Groups extends GroupsAttribute
{
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Metadata -------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class ClassMetadataForLoadTest extends ClassMetadata
{
    public function getX()
    {
        return $this->getData()['X.x'] ?? null;
    }

    public function getY()
    {
        return $this->getData()['Y.y'] ?? null;
    }
}

class PropMetadataForLoadTest extends PropertyMetadata
{
    public function getA()
    {
        return $this->getData()['A.a'] ?? null;
    }

    public function getB()
    {
        return $this->getData()['B.b'] ?? null;
    }

    public function getC()
    {
        return $this->getData()['C.c'] ?? null;
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Classes for extract Metadata -----------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class ForLoadZero
{
}

#[AttrForLoadTestClassX(x: 1)]
#[AttrForLoadTestClassY(y: 2)]
class ForLoad1
{
}

#[AttrForLoadTestClassX(x: 1)]
#[AttrForLoadTestClassY(y: 2)]
class ForLoad2
{
    #[AttrForLoadTestPropA(a: 1)]
    public $foo;
    #[AttrForLoadTestPropB(b: 2)]
    protected $bar;
    #[AttrForLoadTestPropA(a: 3)]
    #[AttrForLoadTestPropB(b: 4)]
    private $baz;
}

#[AttrForLoadTestClassX(x: 1)]
#[AttrForLoadTestClassY(y: 2)]
#[Groups(1)]
class ForLoad3
{
    #[AttrForLoadTestPropA(a: 1)]
    #[Groups(2)]
    public $foo;
    #[AttrForLoadTestPropB(b: 2)]
    #[Groups([3, 4])]
    protected $bar;
    #[AttrForLoadTestPropA(a: 3)]
    #[AttrForLoadTestPropB(b: 4)]
    private $baz;
}

#[Groups(1)]
class ForLoad4
{
    #[AttrForLoadTestMethodA(a: 1)]
    public function getFoo()
    {
    }

    #[AttrForLoadTestMethodA(a: 1)]
    #[Groups(2)]
    protected function getFooButNotLoad($invalid)
    {
    }

    #[AttrForLoadTestMethodB(b: 2)]
    #[Groups([3, 4])]
    private function isBar()
    {
    }

    #[AttrForLoadTestMethodB(b: 3)]
    #[AttrForLoadTestMethodC(c: 4)]
    #[Groups(5)]
    public function hasBaz()
    {
    }

    #[AttrForLoadTestMethodA(a: 5)]
    #[Groups([6, 7, 8])]
    private function setBla()
    {
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Tests ----------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class AttributeLoaderPublicTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected AttributeLoader $loader;

    protected function _before()
    {
        $this->loader = new class extends AttributeLoader {
            protected static function getKnownAttributes(int $target): array
            {
                return match ($target) {
                    \Attribute::TARGET_CLASS => [AttrForLoadTestClassX::class, AttrForLoadTestClassY::class],
                    \Attribute::TARGET_PROPERTY => [AttrForLoadTestPropA::class, AttrForLoadTestPropB::class],
                    \Attribute::TARGET_METHOD => [AttrForLoadTestMethodA::class, AttrForLoadTestMethodB::class, AttrForLoadTestMethodC::class],
                    default => [],
                };
            }

            protected static function getClassMetadataClass(): string
            {
                return ClassMetadataForLoadTest::class;
            }

            protected static function getPropertyMetadataClass(): string
            {
                return PropMetadataForLoadTest::class;
            }
        };
    }

    public function testZero()
    {
        $metadata = $this->loader->loadClassMetadata(ForLoadZero::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $metadata);
        $this->assertCount(0, $metadata->getData());
        $this->assertCount(0, $metadata->getPropertiesMetadata());
    }

    public function testLoadClassMetadata()
    {
        $metadata = $this->loader->loadClassMetadata(ForLoad1::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $metadata);
        $this->assertCount(2, $metadata->getData());
        $this->assertSame(1, $metadata->getX());
        $this->assertSame(2, $metadata->getY());
        $this->assertCount(0, $metadata->getPropertiesMetadata());
    }

    public function testLoadClassPropertyMetadata()
    {
        $metadata = $this->loader->loadClassMetadata(ForLoad2::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $metadata);
        $this->assertCount(2, $metadata->getData());
        $this->assertSame(1, $metadata->getX());
        $this->assertSame(2, $metadata->getY());
        $this->assertCount(3, $metadata->getPropertiesMetadata());

        $this->assertSame('foo', $metadata->getPropertiesMetadata()['foo']->getName());
        $this->assertSame([], $metadata->getPropertiesMetadata()['foo']->getGroups());
        $this->assertSame(1, $metadata->getPropertiesMetadata()['foo']->getA());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getC());

        $this->assertSame('bar', $metadata->getPropertiesMetadata()['bar']->getName());
        $this->assertSame([], $metadata->getPropertiesMetadata()['bar']->getGroups());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getA());
        $this->assertSame(2, $metadata->getPropertiesMetadata()['bar']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getC());

        $this->assertSame('baz', $metadata->getPropertiesMetadata()['baz']->getName());
        $this->assertSame([], $metadata->getPropertiesMetadata()['baz']->getGroups());
        $this->assertSame(3, $metadata->getPropertiesMetadata()['baz']->getA());
        $this->assertSame(4, $metadata->getPropertiesMetadata()['baz']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['baz']->getC());
    }

    public function testLoadClassPropertyWithGroupsMetadata()
    {
        $metadata = $this->loader->loadClassMetadata(ForLoad3::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $metadata);
        $this->assertCount(2, $metadata->getData());
        $this->assertSame(1, $metadata->getX());
        $this->assertSame(2, $metadata->getY());
        $this->assertCount(3, $metadata->getPropertiesMetadata());

        $this->assertSame('foo', $metadata->getPropertiesMetadata()['foo']->getName());
        $this->assertSame(['1', '2'], $metadata->getPropertiesMetadata()['foo']->getGroups());
        $this->assertSame(1, $metadata->getPropertiesMetadata()['foo']->getA());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getC());

        $this->assertSame('bar', $metadata->getPropertiesMetadata()['bar']->getName());
        $this->assertSame(['1', '3', '4'], $metadata->getPropertiesMetadata()['bar']->getGroups());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getA());
        $this->assertSame(2, $metadata->getPropertiesMetadata()['bar']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getC());

        $this->assertSame('baz', $metadata->getPropertiesMetadata()['baz']->getName());
        $this->assertSame(['1'], $metadata->getPropertiesMetadata()['baz']->getGroups());
        $this->assertSame(3, $metadata->getPropertiesMetadata()['baz']->getA());
        $this->assertSame(4, $metadata->getPropertiesMetadata()['baz']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['baz']->getC());
    }

    public function testLoadClassMethodWithGroupsMetadata()
    {
        $metadata = $this->loader->loadClassMetadata(ForLoad4::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $metadata);
        $this->assertCount(0, $metadata->getData());
        $this->assertSame(null, $metadata->getX());
        $this->assertSame(null, $metadata->getY());
        $this->assertCount(4, $metadata->getPropertiesMetadata());

        $this->assertSame('foo', $metadata->getPropertiesMetadata()['foo']->getName());
        $this->assertSame(['1'], $metadata->getPropertiesMetadata()['foo']->getGroups());
        $this->assertSame(1, $metadata->getPropertiesMetadata()['foo']->getA());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['foo']->getC());

        $this->assertFalse(isset($metadata->getPropertiesMetadata()['fooButNotLoad']));

        $this->assertSame('bar', $metadata->getPropertiesMetadata()['bar']->getName());
        $this->assertSame(['1', '3', '4'], $metadata->getPropertiesMetadata()['bar']->getGroups());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getA());
        $this->assertSame(2, $metadata->getPropertiesMetadata()['bar']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bar']->getC());

        $this->assertSame('baz', $metadata->getPropertiesMetadata()['baz']->getName());
        $this->assertSame(['1', '5'], $metadata->getPropertiesMetadata()['baz']->getGroups());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['baz']->getA());
        $this->assertSame(3, $metadata->getPropertiesMetadata()['baz']->getB());
        $this->assertSame(4, $metadata->getPropertiesMetadata()['baz']->getC());

        $this->assertSame('bla', $metadata->getPropertiesMetadata()['bla']->getName());
        $this->assertSame(['1', '6', '7', '8'], $metadata->getPropertiesMetadata()['bla']->getGroups());
        $this->assertSame(5, $metadata->getPropertiesMetadata()['bla']->getA());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bla']->getB());
        $this->assertSame(null, $metadata->getPropertiesMetadata()['bla']->getC());
    }
}
