<?php

namespace Fmasa\AutoDI;

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

        $classes = array_keys($robotLoader->getIndexedClasses());

        foreach($config['services'] as $service) {
            $matchingClasses = preg_grep($this->buildRegex($service['class']), $classes);

            unset($service['class']);

            $services = array_map(function($class) use($service) {
                $service['factory'] = $class;
                return $service;
            }, $matchingClasses);

            Compiler::loadDefinitions(
                $builder,
                $services
            );
        }
    }

    /**
     * @param string $classPattern
     * @return string
     */
    private function buildRegex($classPattern)
    {
        $replacements = [
            '**' => '.*',
            '\\' => '\\\\',
        ];

        $regex = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $classPattern
        );

        return "~^$regex$~";
    }

}
