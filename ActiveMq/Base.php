<?php
namespace Overblog\ActiveMqBundle\ActiveMq;

use Overblog\ActiveMqBundle\Exception\ActiveMqException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Description of Base
 *
 * @author Xavier HAUSHERR
 */
abstract class Base
{
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
        $this->options = new ParameterBag($options);
    }

    /**
     * Return destination string
     * @param string $routing_key
     * @param boolean $concat_key
     * @return string
     * @throws ActiveMqException
     */
    public function getDestination($routing_key = null, $concat_key = false)
    {
        if(in_array($this->options->get('type'), array('queue', 'topic')))
        {
            $destination = sprintf('/%s/%s',
                    $this->options->get('type'),
                    $this->options->get('name')
                );

            if(true === $concat_key && !empty($routing_key))
            {
                $destination = preg_replace('#\\' . $this->options->get('separator') . '>|\*$#', '', $destination);

                $destination .= $this->options->get('separator') . $routing_key;
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
     * @param boolean $concat_key
     */
    public function purge($routing_key = null, $concat_key = false)
    {
        $this->connection->purge($this->getDestination($routing_key, $concat_key));
    }
}