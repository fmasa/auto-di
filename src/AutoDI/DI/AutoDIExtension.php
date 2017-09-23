<?php

namespace Fmasa\AutoDI\DI;

use Fmasa\AutoDI\ClassList;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Loaders\RobotLoader;

class AutoDIExtension extends CompilerExtension
{

    private $defaults = [
        'directories' => [
            '%appDir%',
        ],
        'defaults' => [],
    ];

    public function beforeCompile()
    {
        $config = $this->getConfig($this->defaults);

        $robotLoader = new RobotLoader();
        foreach($config['directories'] as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->rebuild();

        $classes = new ClassList(
            array_keys($robotLoader->getIndexedClasses())
        );

        $builder = $this->getContainerBuilder();

        foreach($config['services'] as $service) {

            list($field, $matchingClasses) = $this->getClasses($service, $classes);

            if(isset($service['exclude'])) {
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

            Compiler::loadDefinitions(
                $builder,
                $services
            );
        }
    }

    /**
     * @param array $service
     * @param ClassList $classes
     * @return array [definition field, Class list]
     */
    private function getClasses(array $service, ClassList $classes)
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
     * @return ClassList
     */
    private function removeExcludedClasses(ClassList $classes, array $exludedPatterns)
    {
        return array_reduce($exludedPatterns, function(ClassList $c, $pattern) {
            return $c->getWithoutClasses($c->getMatching($pattern));
        }, $classes);
    }

}
