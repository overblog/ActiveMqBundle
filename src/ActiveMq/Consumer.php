<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

/**
 * Description of Consumer
 *
 * @author xavier
 */
class Consumer extends Base
{
    /**
     * Handler who treat the message
     * @var ConsumerInterface $handler
     */
    protected $handler;

    /**
     * Routing key (concept from RabbitMQ)
     * @var string
     */
    protected $routing_key;

    /**
     * Init service
     * @param Connection $connection
     * @param ConsumerInterface $handler
     * @param array $options
     */
    public function __construct(Connection $connection, ConsumerInterface $handler, array $options)
    {
        $this->handler = $handler;

        parent::__construct($connection, $options);
    }

    /**
     * Set routing key
     * @param string $routing_key
     */
    public function setRoutingKey($routing_key)
    {
        $this->routing_key = $routing_key;
    }

    /**
     * Consume messages
     * @param int $msgAmount
     */
    public function consume($msgAmount)
    {
        $stomp = $this->connection->getConnection();
        $id = $this->getHeaders()['id'];

        $stomp->subscribe($this->getDestination($this->routing_key), $this->getHeaders());

        // Infinite loop
        if($msgAmount <= 0) $msgAmount = -1;

        while($msgAmount != 0)
        {
            if($stomp->hasFrameToRead())
            {
                // Inject frame into ActimeMQ message
                $frame = $stomp->readFrame();
                $msg = new Message($frame->body, $frame->headers);

                if (false !== call_user_func(array($this->handler, 'execute'), $msg))
                {
                    $stomp->ack($frame);
                }

                $msgAmount --;
            }
        }

        $stomp->unsubscribe($this->getDestination(), array('id' => $id));
    }

    /**
     * Set headers
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array(
            'id' => $this->getDestination($this->routing_key) . microtime(true)
        );

        if($this->options->has('prefetchSize'))
        {
            $headers['activemq.prefetchSize'] = $this->options->get('prefetchSize');
        }

        return $headers;
    }
}

