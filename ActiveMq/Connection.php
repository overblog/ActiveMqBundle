<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

/**
 * Handler for Stomp connections
 *
 * @author Xavier HAUSHERR
 */
class Connection
{
    /**
     * Stomp connection handler
     * @var Stomp
     */
    protected $connection;

    /**
     * Options container
     * @var array
     */
    protected $options = array();

    /**
     * Instanciate connection service
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Return and instanciate connection if needed
     * @return Stomp
     */
    public function getConnection()
    {
        if(is_null($this->connection))
        {
            $this->connection = new \Stomp($this->getBrokerUri(),
                    $this->options['user'],
                    $this->options['password']
                );
        }

        return $this->connection;
    }

    /**
     * Close connection
     */
    public function close()
    {
        unset($this->connection);
    }

    /**
     * Create broker URI - Stomp 1.0 doesn't suport failover :-(
     * http://activemq.apache.org/failover-transport-reference.html
     * @return string
     */
    protected function getBrokerUri()
    {
        return sprintf(
                'tcp://%s:%s',
                $this->options['host'],
                $this->options['port']
            );
    }

    /**
     * Purge queue or topic
     * @param string $queue
     */
    public function purge($queue)
    {
        $stomp = $this->getConnection();

        $stomp->subscribe($queue);

        while($stomp->hasFrame())
        {
            $stomp->ack($stomp->readFrame());
        }

        $stomp->unsubscribe($queue);
    }
}