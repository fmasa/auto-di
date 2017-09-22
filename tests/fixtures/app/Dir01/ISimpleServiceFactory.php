<?php

namespace Fmasa\AutoDI\Tests\Dir01;

interface ISimpleServiceFactory
{

    /**
     * @return SimpleService
     */
    public function create();

}
