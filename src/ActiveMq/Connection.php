<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\ActiveMq;

use CentralDesktop\Stomp\Connection as StompConnection;
use CentralDesktop\Stomp\ConnectionFactory\FactoryI;
use CentralDesktop\Stomp\ConnectionFactory\Failover;
use CentralDesktop\Stomp\ConnectionFactory\Simple;

/**
 * Handler for Stomp connections
 *
 * @author Xavier HAUSHERR
 */
class Connection
{
    /**
     * Stomp connection handler
     *
     * @var StompConnection|null
     */
    protected $connection;

    /**
     * Options container
     *
     * @var array
     */
    protected $options = [];

    /**
     * Instanciate connection service
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Return and instanciate connection if needed
     *
     * @return StompConnection
     */
    public function getConnection()
    {
        if (is_null($this->connection)) {
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
     * @return Simple|Failover
     */
    protected function getConnectionFactory(): FactoryI
    {
        if (1 === count($this->options['servers'])) {
            // Single connection
            return new Simple($this->getBrokerUri(
                current($this->options['servers'])
            ));
        } else {
            // Failover connection
            $servers = [];

            foreach ($this->options['servers'] as $server) {
                $servers[] = $this->getBrokerUri($server);
            }

            return new Failover($servers, $this->options['randomize_failover']);
        }
    }

    /**
     * Create broker URI
     * http://activemq.apache.org/failover-transport-reference.html
     *
     * @return string
     */
    protected function getBrokerUri(array $params)
    {
        $options = [];

        // Base URI
        $uri = 'tcp://'.$params['uri'];

        if (true === $params['useAsyncSend']) {
            $options['useAsyncSend'] = 'true';
        }

        if (!is_null($params['startupMaxReconnectAttempts'])) {
            $options['startupMaxReconnectAttempts'] =
                    $params['startupMaxReconnectAttempts'];
        }

        if (!is_null($params['maxReconnectAttempts'])) {
            $options['maxReconnectAttempts'] =
                    $params['maxReconnectAttempts'];
        }

        if (count($options) > 0) {
            $uri .= '?'.http_build_query($options);
        }

        return $uri;
    }

    /**
     * Purge queue or topic
     *
     * @param string $queue
     */
    public function purge($queue): void
    {
        $stomp = $this->getConnection();
        $header = ['id' => sprintf('purge:%s', $queue)];

        // No need to wait more than 1s if there is no messages in queue
        $stomp->setReadTimeout(1, 0);

        $stomp->subscribe($queue, $header);

        while ($stomp->hasFrameToRead()) {
            $stomp->ack($stomp->readFrame());
        }

        $stomp->unsubscribe($queue, $header);

        // Disconnect & delete connection
        $this->close();
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        if (!is_null($this->connection)) {
            $this->connection->disconnect();
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
