<?php

declare(strict_types=1);

namespace Fmasa\AutoDI\DI;

use Fmasa\AutoDI\ClassList;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers as ConfigHelpers;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Helpers as DIHelpers;
use Nette\DI\ServiceDefinition;
use Nette\Loaders\RobotLoader;

class AutoDIExtension extends CompilerExtension
{

	private $defaults = [
		'registerOnConfiguration' => FALSE,
		'directories' => [
			'%appDir%',
		],
		'ignoreDirectories' => [],
		'defaults' => [],
		'tempDir' => '%tempDir%',
	];

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
		$config = $this->defaults + $this->getConfig();
		return (bool) $config['registerOnConfiguration'];
	}

	private function registerServices(): void
	{
		$builder = $this->getContainerBuilder();
		$config = ConfigHelpers::merge($this->getConfig(), $this->defaults);
		$config = DIHelpers::expand($config, $builder->parameters);
		$robotLoader = new RobotLoader();

		foreach ($config['directories'] as $directory) {
			$robotLoader->addDirectory($directory);
		}

		foreach ($config['ignoreDirectories'] as $ignoredDirectory) {
			$robotLoader->ignoreDirs = $robotLoader->ignoreDirs . ',' . $ignoredDirectory;
		}

		$robotLoader->setTempDirectory($config['tempDir']);
		$robotLoader->rebuild();

		$classes = new ClassList(
			array_keys($robotLoader->getIndexedClasses())
		);


		foreach ($config['services'] as $service) {

			[$field, $matchingClasses] = $this->getClasses($service, $classes);

			if (isset($service['exclude'])) {
				$excluded = $service['exclude'];
				$matchingClasses = $this->removeExcludedClasses($matchingClasses, is_string($excluded) ? [$excluded] : $excluded);
				unset($service['exclude']);
			}

			$matchingClasses = array_filter($matchingClasses->toArray(), function ($class) use ($builder) {
				return count($builder->findByType($class)) === 0;
			});

			$service += $config['defaults'];

			$services = array_map(function ($class) use ($service, $field) {
				$service[$field] = $class;
				return $service;
			}, $matchingClasses);

			foreach ($services as $definitions) {

				if(isset($definitions['implement'])) {
					$definition = new FactoryDefinition();
					$definition->setImplement($definitions['implement']);
				} else if(isset($definitions['class'])) {
					$definition = new ServiceDefinition();
					$definition->setFactory($definitions['class']);
				}
				if (isset($definitions['inject'])) {
					$definition->addTag('nette.inject');
				}
				if(isset($definitions['tags'])){
					$tags = [];
					foreach($definitions['tags'] as $tag) {
						$definition->addTag($tag);
					}

				}

				$builder->addDefinition(null, $definition);
			}
		}
	}

	/**
	 * @param array $service
	 * @param ClassList $classes
	 * @return array [definition field, Class list]
	 */
	private function getClasses(array $service, ClassList $classes): array
	{
		$types = [
			'class' => $classes->getClasses(),
			'implement' => $classes->getInterfaces(),
		];

		if (count(array_intersect_key($service, $types)) !== 1) {
			throw new \InvalidArgumentException(
				'Exactly one of '
				. implode(', ', array_keys($types))
				. ' fields must be set'
			);
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
