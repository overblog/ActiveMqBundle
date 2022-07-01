<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;

/**
 * Description of Publisher
 *
 * @author Xavier HAUSHERR
 */
class Publisher extends Base
{
    /**
     * Publish Message in ActiveMQ
     *
     * @param Message|string $message
     *
     * @throws ActiveMqException
     */
    public function publish($message, ?string $routing_key = null, bool $concat_key = false): bool
    {
        // Create object if text is send
        if (is_string($message)) {
            $message = new Message($message);
        }

        // Add routing key if needed
        if (!empty($routing_key)) {
            $message->headers->set('routing_key', $routing_key);
        }

        $stomp = $this->connection->getConnection();

        if (!$stomp->send(
            $this->getDestination($routing_key, $concat_key),
            $message->getBody(),
            $message->getMessageHeaders()
        )) {
            throw new ActiveMqException('Unable to send message');
        }

        return true;
    }
}
