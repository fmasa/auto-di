<?php

declare(strict_types=1);

namespace Fmasa\AutoDI\DI;

use Fmasa\AutoDI\ClassList;
use Fmasa\AutoDI\Exceptions\IncompleteServiceDefinition;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\Loaders\RobotLoader;
use Nette\Schema\Expect;

class AutoDIExtension extends CompilerExtension
{
    public function getConfigSchema() : Nette\Schema\Schema
    {
        return Expect::structure([
            'services' => Expect::listOf(Expect::array()),
            'registerOnConfiguration' => Expect::bool(false),
            'directories' => Expect::listOf(Expect::string())->default([$this->getContainerBuilder()->parameters['appDir']]),
            'defaults' => Expect::array(),
            'tempDir' => Expect::string($this->getContainerBuilder()->parameters['tempDir']),
        ]);
    }

    public function beforeCompile(): void
    {
        if ( ! $this->shouldRegisterOnConfiguration()) {
            $this->registerServices();
        }
    }

    public function loadConfiguration(): void
    {
        if ($this->shouldRegisterOnConfiguration()) {
            $this->registerServices();
        }
    }

    private function shouldRegisterOnConfiguration(): bool
    {
        return (bool) $this->getConfig()->registerOnConfiguration;
    }

	private function registerServices(): void
	{
        $config = $this->getConfig();

        $robotLoader = new RobotLoader();

        foreach ($config->directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->setTempDirectory($config->tempDir);
        $robotLoader->rebuild();

        $classes = new ClassList(
            array_keys($robotLoader->getIndexedClasses())
        );

        $builder = $this->getContainerBuilder();

        foreach ($config->services as $service) {
            [$field, $matchingClasses] = $this->getClasses($service, $classes);

            if (isset($service['exclude'])) {
                $excluded = $service['exclude'];
                $matchingClasses = $this->removeExcludedClasses($matchingClasses, is_string($excluded) ? [$excluded] : $excluded);
                unset($service['exclude']);
            }

            $matchingClasses = array_filter($matchingClasses->toArray(), function ($class) use ($builder) {
                return count($builder->findByType($class)) === 0;
            });

            $service += $config->defaults;

            $services = array_map(function ($class) use ($service, $field) {
                $service[$field] = $class;
                return $service;
            }, $matchingClasses);

            $this->compiler->loadDefinitionsFromConfig($services);
        }
    }

	/**
     * @return array [definition field, Class list]
     */
    private function getClasses(array $service, ClassList $classes): array
    {
        $types = [
            'class' => $classes->getClasses(),
            'implement' => $classes->getInterfaces(),
        ];

        if (count(array_intersect_key($service, $types)) !== 1) {
            throw IncompleteServiceDefinition::fromDefinition($service);
        }

        foreach($types as $field => $filteredClasses) {
            if(!isset($service[$field])) {
                continue;
            }

            /* @var $filteredClasses ClassList */

            return [
                $field,
                $filteredClasses->getMatching($service[$field]),
            ];
        }

        throw new \RuntimeException('This should never happen');
    }

    /**
     * @param string[] $exludedPatterns
     */
    private function removeExcludedClasses(ClassList $classes, array $exludedPatterns): ClassList
    {
        return array_reduce($exludedPatterns, function(ClassList $c, $pattern) {
            return $c->getWithoutClasses($c->getMatching($pattern));
        }, $classes);
    }

}
