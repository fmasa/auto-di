<?php

declare(strict_types=1);

namespace Fmasa\AutoDI;

use PHPUnit\Framework\TestCase;
use function array_merge;

class ClassListTest extends TestCase
{
    private const CLASSES = [
        Tests\Dir01\SimpleService::class,
        Tests\Dir02\SimpleService::class,
        Tests\Dir01\SimpleService2::class,
        Tests\Dir01\SimpleService\AnotherService::class,
    ];

    public function testClassFilterWithDirectoryWildcard() : void
    {
        $filter = new ClassList(self::CLASSES);

        $classes = $filter->getMatching('Fmasa\AutoDI\**\SimpleService');

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
                Tests\Dir02\SimpleService::class,
            ],
            $classes->toArray()
        );
    }

    public function testClassFilterWithDirectoryWildcardWithClassName() : void
    {
        $filter = new ClassList(self::CLASSES);

        $classes = $filter->getMatching('Fmasa\AutoDI\Tests\Dir**');

        $this->assertSame(
            self::CLASSES,
            $classes->toArray()
        );
    }

    public function testOneLevelWildcardForClassName() : void
    {
        $filter = new ClassList(self::CLASSES);

        $classes = $filter->getMatching('Fmasa\AutoDI\Tests\Dir01\*');

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
                Tests\Dir01\SimpleService2::class,
            ],
            $classes->toArray()
        );
    }

    public function testGroupMatch() : void
    {
        $classes = new ClassList(
            array_merge(self::CLASSES, [Tests\Dir03\ForeignService::class])
        );

        $matching = $classes->getMatching('Fmasa\AutoDI\Tests\{Dir01,Dir02}\**');

        $this->assertSame(self::CLASSES, $matching->toArray());
    }

    public function testFilterClasses() : void
    {
        $list = new ClassList([
            Tests\Dir01\SimpleService::class,
            Tests\Dir01\SimpleTrait::class,
            Tests\Dir01\SimpleInterface::class,
        ]);

        $classes = $list->getClasses();

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
            ],
            $classes->toArray()
        );
    }

    public function testGetClassesFiltersOutAbstractClasses() : void
    {
        $list = new ClassList([
            Tests\Dir01\SimpleService::class,
            Tests\Dir01\AbstractClass::class,
        ]);

        $classes = $list->getClasses();

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
            ],
            $classes->toArray()
        );
    }

    public function testFilterInterfaces() : void
    {
        $list = new ClassList([
            Tests\Dir01\SimpleService::class,
            Tests\Dir01\SimpleTrait::class,
            Tests\Dir01\SimpleInterface::class,
        ]);

        $classes = $list->getInterfaces();

        $this->assertSame(
            [
                Tests\Dir01\SimpleInterface::class,
            ],
            $classes->toArray()
        );
    }
}
