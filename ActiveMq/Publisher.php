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
     * @param string $routing_key
     * @return boolean
     * @throws ActiveMqException
     */
    public function publish($msg, $routing_key = null)
    {
        // Create object if text is send
        if(!is_object($msg))
        {
            $msg = new Message($msg);
        }

        // Add routing key if needed
        if(!empty($routing_key))
        {
            $msg->headers->set('routing_key', $routing_key);
        }

        $stomp = $this->connection->getConnection();

        if(!$stomp->send($this->getDestination($routing_key),
                $msg->getBody(),
                $msg->getMessageHeaders()
            ))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}