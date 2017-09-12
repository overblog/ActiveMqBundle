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
     * @var StompConnection
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
     * @return StompConnection
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
        $uri = 'tcp://%s';

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
                $params['uri']
            );
    }

    /**
     * Purge queue or topic
     * @param string $queue
     */
    public function purge($queue)
    {
        $stomp = $this->getConnection();
        $header = array('id' => sprintf('purge:%s', $queue));

        // No need to wait more than 1s if there is no messages in queue
        $stomp->setReadTimeout(1, 0);

        $stomp->subscribe($queue, $header);

        while($stomp->hasFrameToRead())
        {
            $stomp->ack($stomp->readFrame());
        }

        $stomp->unsubscribe($queue, $header);

        // Disconnect & delete connection
        $this->close();
    }

    /**
     * Close the connection
     */
    public function close()
    {
        if(!is_null($this->connection))
        {
            $this->connection->disconnect();
            $this->connection = null;
        }
    }
}
