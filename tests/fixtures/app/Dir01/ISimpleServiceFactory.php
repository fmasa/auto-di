<?php

declare(strict_types=1);

namespace Fmasa\AutoDI\Tests\Dir01;

interface ISimpleServiceFactory
{
    public function create() : SimpleService;
}
