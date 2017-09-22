<?php

namespace Fmasa\AutoDI\DI;

use Fmasa\AutoDI\ClassFilter;
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

        $classes = new ClassFilter(
            array_keys($robotLoader->getIndexedClasses())
        );

        foreach($config['services'] as $service) {
            $matchingClasses = $classes->filter($service['class']);

            unset($service['class']);

            $services = array_map(function($class) use($service) {
                $service['class'] = $class;
                return $service;
            }, $matchingClasses);

            Compiler::loadDefinitions(
                $builder,
                $services
            );
        }
    }

}
