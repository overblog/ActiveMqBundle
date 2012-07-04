<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\ActiveMq\Base;
use Overblog\ActiveMqBundle\ActiveMq\Connection;
use Overblog\ActiveMqBundle\ActiveMq\ConsumerInterface;
use Overblog\ActiveMqBundle\ActiveMq\Message;

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

    public function consume($msgAmount)
    {
        $stomp = $this->connection->getConnection();

        $stomp->subscribe($this->getDestination());

        // Infinite loop
        if($msgAmount <= 0) $msgAmount = -1;

        while($msgAmount != 0)
        {
            if($stomp->hasFrame())
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

        $stomp->unsubscribe($this->getDestination());
    }
}

