<?php
namespace Overblog\StompBundle\Stomp;

use Overblog\StompBundle\Stomp\Base;
use Overblog\StompBundle\Exception\StompException;
use Overblog\StompBundle\Stomp\Message;
use Overblog\StompBundle\Stomp\Connection;

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
     * @throws StompException
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
            throw new StompException('Unable to send message');
        }

        return true;
    }
}