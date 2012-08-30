<?php
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
     * Message body
     * @var string
     */
    public $body;

    /**
     * Expiration time of the message
     * time in milliseconds to expire the message - 0 means never expire
     * @var int
     */
    protected $expires = 0;

    /**
     * Whether or not the message is persistent
     * @var boolean
     */
    protected $persistent = true;

    /**
     * Priority on the message (0 < 9)
     * value from 0-9
     * @var int
     */
    protected $priority = 4;

    /**
     * Specifies the Message Groups
     * identity of the message group
     * @var string
     */
    protected $groupId;

    /**
     * Specifies the sequence number in the Message Groups
     * @var int
     */
    protected $groupSeq = 0;

    /**
     * The time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     * @var int
     */
    protected $scheduledDelay = 0;

    /**
     * The time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     * @var int
     */
    protected $scheduledPeriod = 0;

    /**
     * The number of times to repeat scheduling a message for delivery
     * @var int
     */
    protected $scheduledRepeat = 0;

    /**
     * Use a Cron entry to set the schedule
     * @var string
     */
    protected $scheduledCron;

    /**
     * Additional headers
     * @var ParameterBag
     */
    public $headers;

    /**
     * Create a new message for sending to Active MQ
     * @param string $body
     * @param array $headers
     */
    public function __construct($body, $headers = array())
    {
        $this->body = $body;
        $this->setHeaders($headers);
    }

    /**
     * Set object with correct headers
     * @param array $headers
     */
    protected function setHeaders(array $headers)
    {
        foreach($headers as $key => $value)
        {
            if(isset($this->$key))
            {
                $this->$key = $value;
                unset($headers[$key]);
            }

            // Clés JMSX
            elseif(isset($this->{'JMSX' . $key}))
            {
                $this->{'JMSX' . $key} = $value;
                unset($headers[$key]);
            }
        }

        $this->headers = new ParameterBag($headers);
    }

    /**
     * Return header value
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->headers->get($key);
    }

    /**
     * Return body
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Return expiration time
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set expiration time
     * @param int $expires
     */
    public function setExpires($expires)
    {
        $this->expires = intval($expires);
    }

    /**
     * Return whether or not the message is persistent
     * @return boolean
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * Set whether or not the message is persistent
     * @param type $persistent
     */
    public function setPersistent($persistent)
    {
        $this->persistent = (bool)$persistent;
    }

    /**
     * Return priority on the message
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority on the message
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = intval($priority);
    }

    /**
     * Get the Message Groups
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Specifies the Message Groups
     * @param string $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * Reset the group affectation by specify the sequence number to -1
     * Next message will be reaffected
     */
    public function closeGroup()
    {
        $this->groupSeq = -1;
    }

    /**
     * Get the time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     * @return int
     */
    public function getScheduledDelay()
    {
        return $this->scheduledDelay;
    }

    /**
     * Set the time in milliseconds that a message will wait
     * before being scheduled to be delivered by the broker
     * @param int $scheduledDelay
     */
    public function setScheduledDelay($scheduledDelay)
    {
        $this->scheduledDelay = intval($scheduledDelay);
    }

    /**
     * Get the time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     * @return int
     */
    public function getScheduledPeriod()
    {
        return $this->scheduledPeriod;
    }

    /**
     * Set the time in milliseconds to wait after the start time
     * to wait before scheduling the message again
     * @param int $scheduledPeriod
     */
    public function setScheduledPeriod($scheduledPeriod)
    {
        $this->scheduledPeriod = intval($scheduledPeriod);
    }

    /**
     * Get the number of times to repeat scheduling a message for delivery
     * @return int
     */
    public function getScheduledRepeat()
    {
        return $this->scheduledRepeat;
    }

    /**
     * Set the number of times to repeat scheduling a message for delivery
     * @param int $scheduledRepeat
     */
    public function setScheduledRepeat($scheduledRepeat)
    {
        $this->scheduledRepeat = intval($scheduledRepeat);
    }

    /**
     * Get Cron entry used to set the schedule
     * @return string
     */
    public function getScheduledCron()
    {
        return $this->scheduledCron;
    }

    /**
     * Use a Cron entry to set the schedule
     * @param string $scheduledCron
     */
    public function setScheduledCron($scheduledCron)
    {
        $this->scheduledCron = $scheduledCron;
    }

    /**
     * Return message options in header format
     * @return array
     */
    public function getMessageHeaders()
    {
        $header = array();

        // Send only header if default value is changed
        if($this->expires != 0)
        {
            $header['expires'] =
                (double)round(microtime(true) * 1000) + $this->expires;
        }

        // Stomp message is not persistent by default
        if($this->persistent)
        {
            $header['persistent'] = 'true';
        }

        if($this->priority != 4)
        {
            $header['priority'] = $this->priority;
        }

        if(!is_null($this->groupId) && !empty($this->groupId))
        {
            $header['JMSXGroupID'] = $this->groupId;
        }

        if($this->groupSeq != 0)
        {
            $header['JMSXGroupSeq'] = $this->groupSeq;
        }

        if($this->scheduledDelay != 0)
        {
            $header['AMQ_SCHEDULED_DELAY'] = $this->scheduledDelay;
        }

        if($this->scheduledPeriod != 0)
        {
            $header['AMQ_SCHEDULED_PERIOD'] = $this->scheduledPeriod;
        }

        if($this->scheduledRepeat != 0)
        {
            $header['AMQ_SCHEDULED_REPEAT'] = $this->scheduledRepeat;
        }

        if(!is_null($this->scheduledCron) && !empty($this->scheduledCron))
        {
            $header['AMQ_SCHEDULED_CRON'] = $this->scheduledCron;
        }

        // Add additional headers
        foreach($this->headers->all() as $key => $var)
        {
            if(!isset($this->$key))
            {
                $header[$key] = $var;
            }
        }

        return $header;
    }
}