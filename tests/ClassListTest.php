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

    public function testTraitsAreExcluded()
    {
        $filter = new ClassList(
            array_merge(
                self::CLASSES,
                [ Tests\Dir01\SimpleTrait::class]
            )
        );

        $classes = $filter->getMatching('Fmasa\AutoDI\Tests\Dir**');

        $this->assertSame(self::CLASSES, $classes->toArray());
    }

}
