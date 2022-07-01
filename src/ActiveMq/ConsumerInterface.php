<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

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
    public function execute(Message $msg);
}

