<?php

namespace Overblog\ActiveMqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OverblogActiveMqBundle extends Bundle
{
    /**
     * Shutdowns the Bundle.
     */
    public function shutdown()
    {
        foreach($this->container->getParameter('activemq.connection') as $connection)
        {
            $this->container->get($connection)->close();
        }
    }
}
