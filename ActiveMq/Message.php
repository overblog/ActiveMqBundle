<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

/**
 * Description of Message
 *
 * @author Xavier HAUSHERR
 */
class Message
{
    /**
     * Message content
     * @var string
     */
    protected $text;

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
     * Priority on the message
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
     * Create a new message for sending to Active MQ
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Return text message
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text message
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
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
     * Return message options in header format
     * @return array
     */
    public function getMessageHeaders()
    {
        $header = array();

        // Send only header if default value is changed
        if($this->expires != 0)
        {
            $header['expires'] = (double)round(microtime(true) * 1000) + $this->expires;
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

        return $header;
    }
}