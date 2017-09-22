<?php

namespace Fmasa\AutoDI;

use Nette\Loaders\RobotLoader;
use PHPUnit\Framework\TestCase;
use Fmasa\AutoDI\Tests;

class ClassFilterTest extends TestCase
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
        $filter = new ClassFilter(self::CLASSES);

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
                Tests\Dir02\SimpleService::class,
            ],
            $filter->filter('Fmasa\AutoDI\**\SimpleService')
        );
    }

    public function testClassFilterWithDirectoryWildcardWithClassName()
    {
        $filter = new ClassFilter(self::CLASSES);

        $this->assertSame(
            self::CLASSES,
            $filter->filter('Fmasa\AutoDI\Tests\Dir**')
        );
    }

    public function testOneLevelWildcardForClassName()
    {
        $filter = new ClassFilter(self::CLASSES);

        $this->assertSame(
            [
                Tests\Dir01\SimpleService::class,
                Tests\Dir01\SimpleService2::class,
            ],
            $filter->filter('Fmasa\AutoDI\Tests\Dir01\*')
        );
    }

    public function testTraitsAreExcluded()
    {
        $filter = new ClassFilter(
            array_merge(
                self::CLASSES,
                [ Tests\Dir01\SimpleTrait::class]
            )
        );

        $this->assertSame(
            self::CLASSES,
            $filter->filter('Fmasa\AutoDI\Tests\Dir**')
        );
    }

}
