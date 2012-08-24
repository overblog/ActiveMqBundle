<?php

namespace Overblog\StompBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OverblogStompExtension extends Extension
{
    const CONNECTION_NAME = 'overblog_stomp.connection.%s';
    const CONNECTION_CLASS = 'overblog_stomp.connection.class';
    const PUBLISHER_NAME = 'overblog_stomp.publisher.%s';
    const PUBLISHER_CLASS = 'overblog_stomp.publisher.class';
    const CONSUMER_NAME = 'overblog_stomp.consumer.%s';
    const CONSUMER_CLASS = 'overblog_stomp.consumer.class';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Register connections
        foreach($config['connections'] as $name => $connection)
        {
            $this->loadConnection($name, $connection, $container);
        }

        //Register publisher
        foreach($config['publishers'] as $name => $producer)
        {
            $this->loadPublisher($name, $producer, $container);
        }

        //Register consumer
        foreach($config['consumers'] as $name => $consumer)
        {
            $this->loadConsumer($name, $consumer, $container);
        }
    }

    /**
     * Load Stomp connections
     * @param  $name
     * @param array $connection
     * @param ContainerBuilder $container
     */
    public function loadConnection($name, Array $connection, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::CONNECTION_CLASS)
        );

        $clientDef->addArgument($connection);

        $container->setDefinition(
            sprintf(self::CONNECTION_NAME, $name),
            $clientDef
        );
    }

    /**
     * Load publisher client
     * @param string $name
     * @param array $producer
     * @param ContainerBuilder $container
     */
    public function loadPublisher($name, Array $producer, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::PUBLISHER_CLASS)
        );

        $clientDef  ->addArgument(new Reference(
                        sprintf(self::CONNECTION_NAME, $producer['connection'])
                    ))
                    ->addArgument($producer['options']);

        $container->setDefinition(
            sprintf(self::PUBLISHER_NAME, $name),
            $clientDef
        );
    }

    /**
     * Load consumer
     * @param string $name
     * @param array $producer
     * @param ContainerBuilder $container
     */
    public function loadConsumer($name, Array $consumer, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::CONSUMER_CLASS)
        );

        $clientDef  ->addArgument(new Reference(
                        sprintf(self::CONNECTION_NAME, $consumer['connection'])
                    ))
                    ->addArgument(new Reference($consumer['handler']))
                    ->addArgument($consumer['options']);

        $container->setDefinition(
            sprintf(self::CONSUMER_NAME, $name),
            $clientDef
        );
    }
}
