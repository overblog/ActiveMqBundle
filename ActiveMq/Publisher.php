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
    /**
     * Connection handler
     * @var Connection $connection
     */
    protected $connection;

    /**
     * Options
     * @var array $options
     */
    protected $options;

    /**
     * Instanciate thee publisher
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(Connection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * Publish Message in ActiveMQ
     * @param Message $msg
     * @return boolean
     * @throws ActiveMqException
     */
    public function publish(Message $msg)
    {
        $stomp = $this->connection->getConnection();

        if(!$stomp->send($this->getDestination(),
                $msg->getText(),
                $msg->getMessageHeaders()
            ))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }

    /**
     * Return destination string
     * @return string
     * @throws ActiveMqException
     */
    public function getDestination()
    {
        if(in_array($this->options['type'], array('queue', 'type')))
        {
            return sprintf('/%s/%s',
                    $this->options['type'],
                    $this->options['name']
                );
        }
        else
        {
            throw new ActiveMqException('Wrong destination type');
        }
    }
}