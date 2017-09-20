<?php

namespace Fmasa\AutoDI;

use Nette\DI\CompilerExtension;

class AutoDIExtension extends CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();

        $builder = $this->getContainerBuilder();

        $serviceId = 0;

        foreach($config['services'] as $service) {
            $builder->addDefinition($this->prefix($serviceId++))
                ->setFactory($service['class']);
        }
    }


}
