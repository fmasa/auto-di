<?php

namespace Fmasa\AutoDI\DI;

use Nette\Configurator;
use Nette\DI\Container;
use Fmasa\AutoDI\Tests;
use PHPUnit\Framework\TestCase;

class AutoDIExtensionTest extends TestCase
{

    public function testOverrideDirectories(): void
    {
        $container = $this->getContainer(__DIR__ . '/alternativeDirectories.neon');

        $container->getByType(Tests\Dir01\AlternativeService::class);
        $container->getByType(Tests\Dir02\AlternativeService::class);
    }

    public function testLoadByClassName(): void
    {
        $container = $this->getContainer(__DIR__ . '/className.neon');
        $container->getByType(Tests\Dir01\SimpleService::class);
    }

    public function testLoadByClassNameWithDirectoryWildcard(): void
    {
        $container = $this->getContainer(__DIR__ . '/directoryWildcard.neon');
        $container->getByType(Tests\Dir01\SimpleService::class);
        $container->getByType(Tests\Dir02\SimpleService::class);
    }

    public function testSetTags(): void
    {
        $container = $this->getContainer(__DIR__ . '/tags.neon');

        $this->assertCount(1, $container->findByTag('test'));
    }

    public function testGeneratedFactory(): void
    {
        $container = $this->getContainer(__DIR__ . '/generatedFactory.neon');

        /* @var $factory Tests\Dir01\ISimpleServiceFactory */
        $factory = $container->getByType(Tests\Dir01\ISimpleServiceFactory::class);
        $this->assertInstanceOf(Tests\Dir01\SimpleService::class, $factory->create());
    }

    public function testAlreadyRegisteredClassOrInterfaceIsNotRegistered(): void
    {
        $container = $this->getContainer(__DIR__ . '/alreadyRegistered.neon');

        $this->assertCount(1, $container->findByType(Tests\Dir01\ISimpleServiceFactory::class));
        $this->assertCount(1, $container->findByType(Tests\Dir01\SimpleService2::class));
    }

    public function testDefaultsAreUsedIfNotOverriden(): void
    {
        $container = $this->getContainer(__DIR__ . '/defaults.neon');

        $services = $container->findByTag('default');

        $this->assertCount(1, $services);
    }

    public function testDefaultsAreOverriden(): void
    {
        $container = $this->getContainer(__DIR__ . '/defaultsOverriden.neon');
        $services = $container->findByTag('new');

        $this->assertEmpty($container->findByTag('default'));
        $this->assertCount(1, $services);
    }

    public function testExcludePattern(): void
    {
        $container = $this->getContainer(__DIR__ . '/excludePattern.neon');

        $container->getByType(Tests\Dir03\ForeignService::class);
        $this->assertNull($container->getByType(Tests\Dir01\SimpleService::class, false));
        $this->assertNull($container->getByType(Tests\Dir02\SimpleService::class, false));
    }

    public function testExcludePatternList(): void
    {
        $container = $this->getContainer(__DIR__ . '/excludePatternList.neon');

        $container->getByType(Tests\Dir03\ForeignService::class);
        $this->assertNull($container->getByType(Tests\Dir01\SimpleService::class, false));
        $this->assertNull($container->getByType(Tests\Dir02\SimpleService::class, false));
    }

    /**
     * There are 2 instances of AutoDIExtension registered, first with registration before compilation
     * and second with registration on configuration. When registering same service by both,
     * only second extension should register it
     */
    public function testRegisterOnConfiguration(): void
    {
        $container = $this->getContainer(__DIR__ . '/onConfiguration.neon');

        $this->assertCount(1, $container->findByTag('onConfiguration'));

        // service registered before compilation
        $this->assertCount(1, $container->findByType(Tests\Dir02\SimpleService::class));
    }

    public function testWorksWithNetteDIDecorator(): void
    {
        $container = $this->getContainer(__DIR__ . '/decorator.neon');

        $this->assertCount(1, $container->findByTag('decorated'));
    }

    private function getContainer(string $configFile, string $appDir = __DIR__ . '/../fixtures/app'): Container
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
