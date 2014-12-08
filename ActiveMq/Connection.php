<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use CentralDesktop\Stomp\Connection as StompConnection;
use CentralDesktop\Stomp\ConnectionFactory\Simple;
use CentralDesktop\Stomp\ConnectionFactory\Failover;

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
            $this->connection = new StompConnection(
                    $this->getConnectionFactory()
                );

            $this->connection->connect(
                    $this->options['user'],
                    $this->options['password'],
                    $this->options['version']
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

    protected function getConnectionFactory()
    {
        if(count($this->options['servers']) == 1)
        {
            // Single connection
            return new Simple($this->getBrokerUri(
                    current($this->options['servers'])
                ));
        }
        else
        {
            // Failover connection
            $servers = array();

            foreach($this->options['servers'] as $server)
            {
                $servers[] = $this->getBrokerUri($server);
            }

            return new Failover($servers, $this->options['randomize_failover']);
        }
    }

    /**
     * Create broker URI
     * http://activemq.apache.org/failover-transport-reference.html
     *
     * @param array $params
     * @return string
     */
    protected function getBrokerUri(array $params)
    {
        $options = array();

        // Base URI
        $uri = 'tcp://%s:%s';

        if(true === $params['useAsyncSend'])
        {
            $options['useAsyncSend'] = 'true';
        }

        if(!is_null($params['startupMaxReconnectAttempts']))
        {
            $options['startupMaxReconnectAttempts'] =
                    $params['startupMaxReconnectAttempts'];
        }

        if(!is_null($params['maxReconnectAttempts']))
        {
            $options['maxReconnectAttempts'] =
                    $params['maxReconnectAttempts'];
        }

        if(count($options) > 0)
        {
            $uri.= '?' . http_build_query($options);
        }

        return sprintf(
                $uri,
                $params['host'],
                $params['port']
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

        while($stomp->hasFrameToRead())
        {
            $stomp->ack($stomp->readFrame());
        }

        $stomp->unsubscribe($queue);
    }
}