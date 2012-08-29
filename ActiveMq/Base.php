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
        $this->options = new ParameterBag($options);
    }

    /**
     * Return destination string
     * @return string
     * @throws ActiveMqException
     */
    public function getDestination()
    {
        if(in_array($this->options->get('type'), array('queue', 'topic')))
        {
            return sprintf('/%s/%s',
                    $this->options->get('type'),
                    $this->options->get('name')
                );
        }
        else
        {
            throw new ActiveMqException('Wrong destination type');
        }
    }

    /**
     * Purge given destination
     * @return type
     */
    public function purge()
    {
        return $this->connection->purge($this->getDestination());
    }
}