<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;

/**
 * Description of Base
 *
 * @author Xavier HAUSHERR
 */
abstract class Base
{
    const SEPARATOR = '.';

    /**
     * Options
     * @var array $options
     */
    protected $options;

    /**
     * Connection handler
     * @var Connection $connection
     */
    protected $connection;

    /**
     * Instanciate
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(Connection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * Return destination string
     * @param string $routing_key
     * @return string
     * @throws ActiveMqException
     */
    public function getDestination($routing_key = null)
    {
        if(in_array($this->options['type'], array('queue', 'type')))
        {
            $destination = sprintf('/%s/%s',
                    $this->options['type'],
                    $this->options['name']
                );

            if(!empty($routing_key))
            {
                $destination = preg_replace('#\\' . self::SEPARATOR . '>|\*$#', '', $destination);

                $destination .= self::SEPARATOR . $routing_key;
            }

            return $destination;
        }
        else
        {
            throw new ActiveMqException('Wrong destination type');
        }
    }

    /**
     * Purge given destination
     * @param string $routing_key
     * @return type
     */
    public function purge($routing_key = null)
    {
        return $this->connection->purge($this->getDestination($routing_key));
    }
}