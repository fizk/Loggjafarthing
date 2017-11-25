<?php

namespace Althingi\Lib;

use Althingi\Service\Assembly;

interface ServiceAssemblyAwareInterface
{
    /**
     * @param \Althingi\Service\Assembly $assembly
     */
    public function setAssemblyService(Assembly $assembly);
}
