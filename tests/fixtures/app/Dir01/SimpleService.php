<?php

namespace Fmasa\AutoDI\Tests\Dir01;

class SimpleService
{
    /** @var bool */
    private $setupMethodCalled = false;

    public function setupMethod() : void
    {
        $this->setupMethodCalled = true;
    }

    public function wasSetupMethodCalled() : bool
    {
        return $this->setupMethodCalled;
    }
}
