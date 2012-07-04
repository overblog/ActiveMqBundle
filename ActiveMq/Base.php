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
     * @return string
     * @throws ActiveMqException
     */
    public function getDestination()
    {
        if(in_array($this->options['type'], array('queue', 'type')))
        {
            return sprintf('/%s/%s',
                    $this->options['type'],
                    $this->options['name']
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