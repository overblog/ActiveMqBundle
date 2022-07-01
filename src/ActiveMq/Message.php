<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\ActiveMq;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Description of Active MQ Message
 *
 * @author Xavier HAUSHERR
 */
class Message
{
    /**
     * Message Type Text (default)
     */
    public const TYPE_TEXT = 'text';

    /**
     * Message Type Bytes
     */
    public const TYPE_BYTES = 'bytes';

    /**
     * Message body
     *
     * @var string
     */
    public $body;

    /**
     * Expiration time of the message
     * time in milliseconds to expire the message - 0 means never expire
     *
     * @var int
     */
    protected $expires = 0;

    /**
     * Whether or not the message is persistent
     *
     * @var bool
     */
    protected $persistent = true;

    /**
     * Priority on the message (0 < 9)
     * value from 0-9
     *
     * @var int
     */
    protected $priority = 4;

    /**
     * Specifies the Message Groups
     * identity of the message group
     *
     * @var string
     */
    protected $groupId;

    /**
     * Specifies the sequence number in the Message Groups
     *
     * @var int
     */
    protected $groupSeq = 0;

    /**
     * The time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     *
     * @var int
     */
    protected $scheduledDelay = 0;

    /**
     * The time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     *
     * @var int
     */
    protected $scheduledPeriod = 0;

    /**
     * The number of times to repeat scheduling a message for delivery
     *
     * @var int
     */
    protected $scheduledRepeat = 0;

    /**
     * Use a Cron entry to set the schedule
     *
     * @var string
     */
    protected $scheduledCron;

    /**
     * Additional headers
     *
     * @var ParameterBag
     */
    public $headers;

    /**
     * Create a new message for sending to Active MQ
     *
     * @param string $body
     * @param array  $headers
     */
    public function __construct($body, $headers = [])
    {
        $this->body = $body;
        $this->setHeaders($headers);
    }

    /**
     * Set object with correct headers
     */
    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
                unset($headers[$key]);
            }

            // Clés JMSX
            elseif (isset($this->{'JMSX'.$key})) {
                $this->{'JMSX'.$key} = $value;
                unset($headers[$key]);
            }
        }

        $this->headers = new ParameterBag($headers);
    }

    /**
     * Return header value
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        return $this->headers->get($key);
    }

    /**
     * Return body
     *
     * @return string
     */
    public function getBody()
    {
        return (string) $this->body;
    }

    /**
     * Set body
     *
     * @param string $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * Return expiration time
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set expiration time
     *
     * @param int $expires
     */
    public function setExpires($expires): void
    {
        $this->expires = intval($expires);
    }

    /**
     * Return whether or not the message is persistent
     *
     * @return bool
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * Set whether or not the message is persistent
     *
     * @param bool $persistent
     */
    public function setPersistent($persistent): void
    {
        $this->persistent = (bool) $persistent;
    }

    /**
     * Return priority on the message
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority on the message
     *
     * @param int $priority
     */
    public function setPriority($priority): void
    {
        $this->priority = intval($priority);
    }

    /**
     * Get the Message Groups
     *
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Specifies the Message Groups
     *
     * @param string $groupId
     */
    public function setGroupId($groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * Reset the group affectation by specify the sequence number to -1
     * Next message will be reaffected
     */
    public function closeGroup(): void
    {
        $this->groupSeq = -1;
    }

    /**
     * Get the time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     *
     * @return int
     */
    public function getScheduledDelay()
    {
        return $this->scheduledDelay;
    }

    /**
     * Set the time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     *
     * @param int $scheduledDelay
     */
    public function setScheduledDelay($scheduledDelay): void
    {
        $this->scheduledDelay = intval($scheduledDelay);
    }

    /**
     * Get the time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     *
     * @return int
     */
    public function getScheduledPeriod()
    {
        return $this->scheduledPeriod;
    }

    /**
     * Set the time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     *
     * @param int $scheduledPeriod
     */
    public function setScheduledPeriod($scheduledPeriod): void
    {
        $this->scheduledPeriod = intval($scheduledPeriod);
    }

    /**
     * Get the number of times to repeat scheduling a message for delivery
     *
     * @return int
     */
    public function getScheduledRepeat()
    {
        return $this->scheduledRepeat;
    }

    /**
     * Set the number of times to repeat scheduling a message for delivery
     *
     * @param int $scheduledRepeat
     */
    public function setScheduledRepeat($scheduledRepeat): void
    {
        $this->scheduledRepeat = intval($scheduledRepeat);
    }

    /**
     * Get Cron entry used to set the schedule
     *
     * @return string
     */
    public function getScheduledCron()
    {
        return $this->scheduledCron;
    }

    /**
     * Use a Cron entry to set the schedule
     *
     * @param string $scheduledCron
     */
    public function setScheduledCron($scheduledCron): void
    {
        $this->scheduledCron = $scheduledCron;
    }

    /**
     * Return message Type
     *
     * @return string
     */
    public function getMessageType()
    {
        if ($this->headers->has('amq-msg-type')) {
            return $this->headers->get('amq-msg-type');
        } else {
            return self::TYPE_TEXT;
        }
    }

    /**
     * Set message type to text
     */
    public function setAsTextMessage(): void
    {
        $this->headers->set('amq-msg-type', self::TYPE_TEXT);
    }

    /**
     * Set message type to bytes
     */
    public function setAsBytesMessage(): void
    {
        $this->headers->set('amq-msg-type', self::TYPE_BYTES);
    }

    /**
     * Return message options in header format
     *
     * @return array
     */
    public function getMessageHeaders()
    {
        $header = [];

        // Send only header if default value is changed
        if (0 != $this->expires) {
            $header['expires'] =
                (float) round(microtime(true) * 1000) + $this->expires;
        }

        // Stomp message is not persistent by default
        if ($this->persistent) {
            $header['persistent'] = 'true';
        }

        if (4 != $this->priority) {
            $header['priority'] = $this->priority;
        }

        if (!is_null($this->groupId) && !empty($this->groupId)) {
            $header['JMSXGroupID'] = $this->groupId;
        }

        if (0 != $this->groupSeq) {
            $header['JMSXGroupSeq'] = $this->groupSeq;
        }

        if (0 != $this->scheduledDelay) {
            $header['AMQ_SCHEDULED_DELAY'] = $this->scheduledDelay;
        }

        if (0 != $this->scheduledPeriod) {
            $header['AMQ_SCHEDULED_PERIOD'] = $this->scheduledPeriod;
        }

        if (0 != $this->scheduledRepeat) {
            $header['AMQ_SCHEDULED_REPEAT'] = $this->scheduledRepeat;
        }

        if (!is_null($this->scheduledCron) && !empty($this->scheduledCron)) {
            $header['AMQ_SCHEDULED_CRON'] = $this->scheduledCron;
        }

        // Add additional headers
        foreach ($this->headers->all() as $key => $var) {
            if (!isset($this->$key)) {
                $header[$key] = $var;
            }
        }

        return $header;
    }
}
