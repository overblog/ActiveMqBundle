<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\ActiveMq\Message;

/**
 * Description of Consumer
 *
 * @author Xavier HAUSHERR
 */
interface ConsumerInterface
{
    /**
     * Consume message
     */
    function execute(Message $msg);
}

