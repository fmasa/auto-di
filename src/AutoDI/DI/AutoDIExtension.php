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
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);

        $builder = $this->getContainerBuilder();

        $robotLoader = new RobotLoader();
        foreach($config['directories'] as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->rebuild();

        $classes = new ClassList(
            array_keys($robotLoader->getIndexedClasses())
        );

        foreach($config['services'] as $service) {

            list($field, $matchingClasses) = $this->getClasses($service, $classes);

            $services = array_map(function ($class) use ($service, $field) {
                $service[$field] = $class;
                return $service;
            }, $matchingClasses->toArray());

            Compiler::loadDefinitions(
                $builder,
                $services
            );
        }
    }

    /**
     * @param array $service
     * @param ClassList $classes
     * @return array [definition field, matching classes]
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

            return [
                $field,
                $filteredClasses->getMatching($service[$field]),
            ];
        }

        throw new \RuntimeException('This should never happen');
    }

}
