<?php

namespace Fmasa\AutoDI;

use Nette\Loaders\RobotLoader;
use PHPUnit\Framework\TestCase;
use Fmasa\AutoDI\Tests;

class ClassListTest extends TestCase
{

    const CLASSES = [
        Tests\Dir01\SimpleService::class,
        Tests\Dir02\SimpleService::class,
        Tests\Dir01\SimpleService2::class,
        Tests\Dir01\SimpleService\AnotherService::class,
    ];

    protected function setUp()
    {
        $loader = new RobotLoader();
        $loader->addDirectory(__DIR__ . '/fixtures');
        $loader->setTempDirectory(__DIR__ . '/temp');
        $loader->register();
    }

    public function testClassFilterWithDirectoryWildcard()
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

    public function testClassFilterWithDirectoryWildcardWithClassName()
    {
        $filter = new ClassList(self::CLASSES);

        $classes = $filter->getMatching('Fmasa\AutoDI\Tests\Dir**');

        $this->assertSame(
            self::CLASSES,
            $classes->toArray()
        );
    }

    public function testOneLevelWildcardForClassName()
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

    public function testFilterClasses()
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

    public function testFilterInterfaces()
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
