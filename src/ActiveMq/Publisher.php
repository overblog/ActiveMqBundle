<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;

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
     * @param boolean $concat_key
     * @return boolean
     * @throws ActiveMqException
     */
    public function publish($msg, $routing_key = null, $concat_key = false)
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

        if(!$stomp->send($this->getDestination($routing_key, $concat_key),
                $msg->getBody(),
                $msg->getMessageHeaders()
            ))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}