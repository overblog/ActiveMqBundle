<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;
use Overblog\ActiveMqBundle\ActiveMq\Message;

/**
 * Description of Publisher
 *
 * @author Xavier HAUSHERR
 */
class Publisher
{
    public function __construct()
    {

    }

    public function publish($queue, Message $msg)
    {
        $stomp = new \Stomp('tcp://localhost:61613');

        //$msg->setPersistent(1);

        var_dump($msg->getMessageHeaders());

        if(!$stomp->send($queue, $msg->getText(), $msg->getMessageHeaders()))
        {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}