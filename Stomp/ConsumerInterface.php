<?php
namespace Overblog\StompBundle\Stomp;

use Overblog\StompBundle\Stomp\Message;

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

