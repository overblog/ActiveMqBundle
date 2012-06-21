<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;
use Overblog\ActiveMqBundle\ActiveMq\Message;
use Overblog\ActiveMqBundle\ActiveMq\Connection;

/**
 * Description of Publisher
 *
 * @author Xavier HAUSHERR
 */
class Publisher
{
    protected $connection;
    protected $options;

    public function __construct(Connection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    public function publish(Message $msg)
    {
        $stomp = $this->connection->getConnection();

        if(!$stomp->send('/queue/hub.xavier', $msg->getText(), $msg->getMessageHeaders()))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}