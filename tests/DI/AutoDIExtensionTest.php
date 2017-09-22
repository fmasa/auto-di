<?php

namespace Fmasa\AutoDI\DI;

use Nette\Configurator;
use Nette\DI\Container;
use Fmasa\AutoDI\Tests;
use PHPUnit\Framework\TestCase;

class AutoDIExtensionTest extends TestCase
{

    public function testOverrideDirectories()
    {
        $container = $this->getContainer(__DIR__ . '/alternativeDirectories.neon');

        $container->getByType(Tests\Dir01\AlternativeService::class);
        $container->getByType(Tests\Dir02\AlternativeService::class);
    }

    public function testLoadByClassName()
    {
        $container = $this->getContainer(__DIR__ . '/className.neon');
        $container->getByType(Tests\Dir01\SimpleService::class);
    }

    public function testLoadByClassNameWithDirectoryWildcard()
    {
        $container = $this->getContainer(__DIR__ . '/directoryWildcard.neon');
        $container->getByType(Tests\Dir01\SimpleService::class);
        $container->getByType(Tests\Dir02\SimpleService::class);
    }

    public function testSetTags()
    {
        $container = $this->getContainer(__DIR__ . '/tags.neon');

        $this->assertCount(1, $container->findByTag('test'));
    }

    public function testGeneratedFactory()
    {
        $container = $this->getContainer(__DIR__ . '/generatedFactory.neon');

        /* @var $factory Tests\Dir01\ISimpleServiceFactory */
        $factory = $container->getByType(Tests\Dir01\ISimpleServiceFactory::class);
        $this->assertInstanceOf(Tests\Dir01\SimpleService::class, $factory->create());
    }

    /**
     * @param string $configFile
     * @param string $appDir
     * @return Container
     */
    private function getContainer($configFile, $appDir = __DIR__ . '/../fixtures/app')
    {
        $configurator = new Configurator();
        $configurator->setTempDirectory(__DIR__ . '/../temp');
        $configurator->setDebugMode(true);


        $robotLoader = $configurator->createRobotLoader();
        $robotLoader->addDirectory(__DIR__ . '/../fixtures');
        $robotLoader->register();

        $configurator->addConfig(__DIR__ . '/base.neon');
        $configurator->addParameters([
            'appDir' => $appDir,
        ]);
        $configurator->addConfig($configFile);

        return $configurator->createContainer();
    }

}
