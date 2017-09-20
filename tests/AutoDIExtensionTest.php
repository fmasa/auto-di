<?php

namespace Fmasa\AutoDI;

use Nette\Configurator;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase;

class AutoDIExtensionTest extends TestCase
{

    public function testLoadByClassName()
    {
        $container = $this->getContainer(__DIR__ . '/className.neon');
        $container->getByType(Tests\Dir01\SimpleService::class);
    }

    /**
     * @param string $configFile
     * @param string $appDir
     * @return Container
     */
    private function getContainer($configFile, $appDir = __DIR__ . '/app')
    {
        $configurator = new Configurator();
        $configurator->setTempDirectory(__DIR__ . '/temp');
        $configurator->setDebugMode(true);

        $robotLoader = $configurator->createRobotLoader();
        $robotLoader->addDirectory($appDir);
        $robotLoader->register();

        $configurator->addConfig(__DIR__ . '/base.neon');
        $configurator->addConfig($configFile);

        return $configurator->createContainer();
    }

}
