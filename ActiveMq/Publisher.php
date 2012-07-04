<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\ActiveMq\Base;
use Overblog\ActiveMqBundle\Exception\ActiveMqException;
use Overblog\ActiveMqBundle\ActiveMq\Message;
use Overblog\ActiveMqBundle\ActiveMq\Connection;

/**
 * Description of Publisher
 *
 * @author Xavier HAUSHERR
 */
class Publisher extends Base
{
    /**
     * Publish Message in ActiveMQ
     * @param mixed $msg
     * @return boolean
     * @throws ActiveMqException
     */
    public function publish($msg)
    {
        if(!is_object($msg))
        {
            $msg = new Message($msg);
        }

        $stomp = $this->connection->getConnection();

        if(!$stomp->send($this->getDestination(),
                $msg->getBody(),
                $msg->getMessageHeaders()
            ))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}