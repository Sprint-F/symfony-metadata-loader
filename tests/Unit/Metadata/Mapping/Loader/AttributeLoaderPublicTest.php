<?php

namespace SprintF\Tests\Unit\Metadata\Mapping\Loader;

use JetBrains\PhpStorm\NoReturn;
use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\ClassMetadataInterface;
use SprintF\Metadata\Mapping\Loader\AttributeLoader;
use SprintF\Metadata\Mapping\PropertyMetadata;
use SprintF\Metadata\Mapping\PropertyMetadataInterface;
use SprintF\Tests\Support\UnitTester;

// ---------------------------------------------------------------------------------------------------------------------
// ---- Attributes -----------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForLoadTestClassX extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $x,
        public readonly mixed $y = null,
        public readonly mixed $z = null,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'X';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class AttrForLoadTestClassY extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $y,
        private readonly ?array $groups = [MetadataAttribute::DEFAULT_GROUP],
    ) {
    }

    public function getKey(): string
    {
        return 'Y';
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AttrForLoadTestPropA extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $a,
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
class AttrForLoadTestPropB extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $b,
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
class AttrForLoadTestMethodA extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $a,
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
class AttrForLoadTestMethodB extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $b,
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
class AttrForLoadTestMethodC extends MetadataAttribute
{
    public function __construct(
        public readonly mixed $c,
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

class ClassMetadataForLoadTest extends ClassMetadata implements ClassMetadataInterface
{
    public function getXByDefaultGroup()
    {
        return $this->getDataByGroups([MetadataAttribute::DEFAULT_GROUP])['X.x'] ?? null;
    }

    public function getYByDefaultGroup()
    {
        return $this->getDataByGroups([MetadataAttribute::DEFAULT_GROUP])['Y.y'] ?? null;
    }
}

class PropMetadataForLoadTest extends PropertyMetadata implements PropertyMetadataInterface
{
    public function getAByDefaultGroup()
    {
        return $this->getDataByGroups([MetadataAttribute::DEFAULT_GROUP])['A.a'] ?? null;
    }

    public function getBByDefaultGroup()
    {
        return $this->getDataByGroups([MetadataAttribute::DEFAULT_GROUP])['B.b'] ?? null;
    }

    public function getCByDefaultGroup()
    {
        return $this->getDataByGroups([MetadataAttribute::DEFAULT_GROUP])['C.c'] ?? null;
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// ---- Classes for extract Metadata -----------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------

class ForLoadZero
{
}

#[AttrForLoadTestClassX(x: 1, y: 2, z: 3, groups: ['group_1'])]
#[AttrForLoadTestClassY(y: 3, groups: ['group_1', 'group_2'])]
class ForLoad1
{
}

#[AttrForLoadTestClassX(x: 1, z: 3)]
#[AttrForLoadTestClassY(y: 2, groups: [MetadataAttribute::DEFAULT_GROUP, 'group_1', 'group_2'])]
class ForLoad2
{
    #[AttrForLoadTestPropA(a: 1)]
    public $foo;

    #[AttrForLoadTestPropB(b: 2, groups: [MetadataAttribute::DEFAULT_GROUP, 'group_2'])]
    protected $bar;

    #[AttrForLoadTestPropA(a: 3)]
    #[AttrForLoadTestPropB(b: 4, groups: ['group_1', 'group_2'])]
    private $baz;
}

#[AttrForLoadTestClassX(x: 1, groups: ['group_1'])]
#[AttrForLoadTestClassY(y: 2, groups: ['group_2'])]
class ForLoad3
{
    #[AttrForLoadTestPropA(a: 1, groups: ['group_1'])]
    public $foo;

    #[AttrForLoadTestPropB(b: 2, groups: ['group_1'])]
    protected $bar;

    #[AttrForLoadTestPropA(a: 3, groups: ['group_1'])]
    #[AttrForLoadTestPropB(b: 4, groups: ['group_2'])]
    private $baz;
}

class ForLoad4
{
    #[AttrForLoadTestMethodA(a: 1, groups: ['group_1'])]
    public function getFoo()
    {
    }

    #[AttrForLoadTestMethodA(a: 1, groups: ['group_2'])]
    protected function getFooButNotLoad($invalid)
    {
    }

    #[AttrForLoadTestMethodB(b: 3, groups: ['group_1'])]
    #[AttrForLoadTestMethodC(c: 4, groups: ['group_5'])]
    public function getBaz()
    {
    }

    #[AttrForLoadTestMethodA(a: 5, groups: ['group_6'])]
    private function getBla()
    {
    }
}

#[AttrForLoadTestClassX(x: 1, groups: ['group_1'])]
#[AttrForLoadTestClassY(y: 2, groups: ['*'])]
class ForLoad5
{
    #[AttrForLoadTestPropA(a: 1, groups: ['*'])]
    public $foo;

    #[AttrForLoadTestPropB(b: 2, groups: ['group_2'])]
    protected $bar;

    #[AttrForLoadTestPropB(b: 4, groups: ['group_2'])]
    private $baz;

    #[AttrForLoadTestPropB(b: 1, groups: ['group_3'])]
    private $bla;
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
        $classMetadata = $this->loader->loadClassMetadata(ForLoadZero::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $classMetadata);
        $this->assertCount(0, $classMetadata->getData());
        $this->assertCount(0, $classMetadata->getPropertiesMetadata());
    }

    public function testLoadClassMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad1::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $classMetadata);

        // Проверяем работу getDataByGroups
        $metadataByGroup = $classMetadata->getDataByGroups(['group_1']);
        $this->assertCount(4, $metadataByGroup);
        $this->assertEquals(1, $metadataByGroup['X.x']);
        $this->assertEquals(3, $metadataByGroup['X.z']);
        $this->assertCount(0, $classMetadata->getPropertiesMetadata());
    }

    public function testLoadClassPropertyMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad2::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $classMetadata);

        // Проверяем данные класса по группе group_1
        $metadataByGroup = $classMetadata->getDataByGroups(['group_1']);
        $this->assertCount(4, $metadataByGroup);
        $this->assertEquals(2, $metadataByGroup['Y.y']);

        // Проверяем данные класса по дефолтной группе
        $metadataByGroup = $classMetadata->getDataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertCount(4, $metadataByGroup);
        $this->assertEquals(1, $metadataByGroup['X.x']);
        $this->assertEquals(3, $metadataByGroup['X.z']);

        // Проверяем свойства
        $propertiesMetadata = $classMetadata->getPropertiesMetadataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertCount(3, $propertiesMetadata);

        // Проверяем foo
        $this->assertArrayHasKey('foo', $propertiesMetadata);
        $metadataOfFooProperty = $propertiesMetadata['foo']->getDataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertEquals(1, $metadataOfFooProperty['A.a']);
        $this->assertArrayNotHasKey('B.b', $metadataOfFooProperty);

        // Проверяем bar
        $this->assertArrayHasKey('bar', $propertiesMetadata);
        $metadataOfBarProperty = $propertiesMetadata['bar']->getDataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertArrayNotHasKey('A.a', $metadataOfBarProperty);
        $this->assertEquals(2, $metadataOfBarProperty['B.b']);

        // Проверяем baz
        $this->assertArrayHasKey('baz', $propertiesMetadata);
        $metadataOfBazProperty = $propertiesMetadata['baz']->getDataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertEquals(3, $metadataOfBazProperty['A.a']);
    }

    public function testLoadClassPropertyWithGroupsMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad3::class);

        // Проверяем данные класса для группы 1
        $metadataByGroup = $classMetadata->getDataByGroups(['group_1']);
        $this->assertCount(3, $metadataByGroup);
        $this->assertEquals(1, $metadataByGroup['X.x']);

        // Проверяем свойства для разных групп (группы 1, 2, 4)
        $propertiesMetadataByGroups = $classMetadata->getPropertiesMetadataByGroups(['group_1', 'group_2', 'group_4']);
        $this->assertCount(3, $propertiesMetadataByGroups);

        // Проверяем foo (группы 1, 2)
        $metadataOfFooProperty = $propertiesMetadataByGroups['foo']->getDataByGroups(['group_1', 'group_2']);
        $this->assertEquals(1, $metadataOfFooProperty['A.a']);

        // Проверяем bar (группы 1, 3, 4)
        $metadataOfBarProperty = $propertiesMetadataByGroups['bar']->getDataByGroups(['group_1', 'group_3', 'group_4']);
        $this->assertEquals(2, $metadataOfBarProperty['B.b']);

        // Проверяем baz (только группа 1)
        $metadataOfBazProperty = $propertiesMetadataByGroups['baz']->getDataByGroups(['group_1']);
        $this->assertEquals(3, $metadataOfBazProperty['A.a']);
    }

    public function testLoadClassMethodWithGroupsMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad4::class);

        // Проверяем методы для разных групп
        $methodsMetadata = $classMetadata->getPropertiesMetadataByGroups(['group_1', 'group_3', 'group_4', 'group_6', 'group_8']);
        $this->assertCount(3, $methodsMetadata);

        // Проверяем getFoo (группа 1)
        $this->assertArrayHasKey('foo', $methodsMetadata);
        $fooData = $methodsMetadata['foo']->getDataByGroups(['group_1']);
        $this->assertEquals(1, $fooData['A.a']);

        // Проверяем что метод с invalid параметром не загружен
        $this->assertArrayNotHasKey('getFooButNotLoad', $methodsMetadata);

        // Проверяем getBaz (группы 1, 5)
        $bazData = $methodsMetadata['baz']->getDataByGroups(['group_1', 'group_5']);
        $this->assertEquals(3, $bazData['B.b']);
        $this->assertEquals(4, $bazData['C.c']);

        // Проверяем getBla (группы 1, 6, 7, 8)
        $blaData = $methodsMetadata['bla']->getDataByGroups(['group_6']);
        $this->assertEquals(5, $blaData['A.a']);
    }

    public function testDefaultGroupMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad2::class);

        $this->assertInstanceOf(ClassMetadataForLoadTest::class, $classMetadata);

        // Проверяем что данные попали в группу '*'
        $metadataByDefault = $classMetadata->getDataByGroups([MetadataAttribute::DEFAULT_GROUP]);
        $this->assertCount(4, $metadataByDefault);
        $this->assertArrayHasKey('X.x', $metadataByDefault);
        $this->assertArrayHasKey('Y.y', $metadataByDefault);
        $this->assertEquals(1, $metadataByDefault['X.x']);
        $this->assertEquals(2, $metadataByDefault['Y.y']);
    }

    public function testSpecificGroupsMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad1::class);

        // Проверяем данные для group_1
        $metadataByGroup = $classMetadata->getDataByGroups(['group_1']);
        $this->assertNotEmpty($metadataByGroup);
        $this->assertEquals(1, $metadataByGroup['X.x']);
        $this->assertEquals(3, $metadataByGroup['Y.y']);

        // Проверяем данные для group_2
        $metadataByGroup = $classMetadata->getDataByGroups(['group_2']);
        $this->assertNotEmpty($metadataByGroup);
        $this->assertEquals(3, $metadataByGroup['Y.y']);
    }

    public function testMultipleGroupsMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad1::class);

        // Проверяем данные для нескольких групп
        $metadataByMultiGroup = $classMetadata->getDataByGroups(['group_1', 'group_2']);
        $this->assertCount(4, $metadataByMultiGroup);
        $this->assertEquals(1, $metadataByMultiGroup['X.x']);
        $this->assertEquals(3, $metadataByMultiGroup['Y.y']);
    }

    public function testEmptyGroupsMetadata()
    {
        $classMetadata = $this->loader->loadClassMetadata(ForLoad1::class);

        // Проверяем что для несуществующей группы данных нет
        $metadataByNonExistentGroup = $classMetadata->getDataByGroups(['non_existent_group']);
        $this->assertCount(0, $metadataByNonExistentGroup);
    }

    public function testGlobalMetadataIsIncludedInGroups()
    {
        // Загружаем метаданные класса ForLoad5
        $classMetadata = $this->loader->loadClassMetadata(ForLoad5::class);

        // Проверяем метаданные для 'group_1'
        $methodsMetadata = $classMetadata->getPropertiesMetadataByGroups(['group_1']);
        $metadataOfFooProperty = $methodsMetadata['foo']->getDataByGroups(['group_1']);
        $this->assertCount(1, $methodsMetadata);
        $this->assertEquals(1, $metadataOfFooProperty['A.a']);

        // Проверяем метаданные для 'group_2'
        $methodsMetadata = $classMetadata->getPropertiesMetadataByGroups(['group_2']);
        $metadataOfFooProperty = $methodsMetadata['foo']->getDataByGroups(['group_2']);
        $metadataOfBarProperty = $methodsMetadata['bar']->getDataByGroups(['group_2']);
        $metadataOfBazProperty = $methodsMetadata['baz']->getDataByGroups(['group_2']);

        $this->assertCount(3, $methodsMetadata);
        $this->assertEquals(2, $metadataOfBarProperty['B.b']);
        $this->assertEquals(1, $metadataOfFooProperty['A.a']);
        $this->assertEquals(4, $metadataOfBazProperty['B.b']);

        // Проверяем метаданные для 'group_3'
        $methodsMetadata = $classMetadata->getPropertiesMetadataByGroups(['group_3']);
        $this->assertCount(2, $methodsMetadata);

        // Проверяем получение всех данных по 'group_1', включая общие (*)
        $metadataByGroup = $classMetadata->getDataByGroups(['group_1']);
        $this->assertCount(4, $metadataByGroup);
        $this->assertEquals(2, $metadataByGroup['Y.y']);
        $this->assertEquals(1, $metadataByGroup['X.x']);
    }
}
