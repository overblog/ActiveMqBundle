<?php

namespace Overblog\ActiveMqBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
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
class OverblogActiveMqExtension extends Extension
{
    const CONNECTION_NAME = 'overblog_active_mq.connection.%s';
    const CONNECTION_CLASS = 'overblog_active_mq.connection.class';
    const PUBLISHER_NAME = 'overblog_active_mq.publisher.%s';
    const PUBLISHER_CLASS = 'overblog_active_mq.publisher.class';
    const CONSUMER_NAME = 'overblog_active_mq.consumer.%s';
    const CONSUMER_CLASS = 'overblog_active_mq.consumer.class';
    const TAG = 'activemq.connection';

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

        $container->setParameter(self::TAG, array_keys(
            $container->findTaggedServiceIds(self::TAG)
        ));
    }

    /**
     * Load Stomp connections
     * @param string $name
     * @param array $connection
     * @param ContainerBuilder $container
     */
    public function loadConnection($name, array $connection, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::CONNECTION_CLASS)
        );

        $clientDef->addArgument($connection);
        $clientDef->addTag(self::TAG);

        $container->setDefinition(
            sprintf(self::CONNECTION_NAME, $name),
            $clientDef
        );

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias(
            $clientDef->getClass(),
            new Alias(sprintf(self::CONNECTION_NAME, $name), true)
        );
    }

    /**
     * Load publisher client
     * @param string $name
     * @param array $publisher
     * @param ContainerBuilder $container
     */
    public function loadPublisher($name, array $publisher, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::PUBLISHER_CLASS)
        );

        $clientDef->addArgument(new Reference(
            sprintf(self::CONNECTION_NAME, $publisher['connection'])
        ))
            ->addArgument($publisher['options']);

        $container->setDefinition(
            sprintf(self::PUBLISHER_NAME, $name),
            $clientDef
        );

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias(
            $clientDef->getClass(),
            new Alias(sprintf(self::PUBLISHER_NAME, $name), true)
        );
    }

    /**
     * Load consumer
     * @param string $name
     * @param array $consumer
     * @param ContainerBuilder $container
     */
    public function loadConsumer($name, array $consumer, ContainerBuilder $container)
    {
        $clientDef = new Definition(
            $container->getParameter(self::CONSUMER_CLASS)
        );

        $clientDef->addArgument(new Reference(
            sprintf(self::CONNECTION_NAME, $consumer['connection'])
        ))
            ->addArgument(new Reference($consumer['handler']))
            ->addArgument($consumer['options']);

        $container->setDefinition(
            sprintf(self::CONSUMER_NAME, $name),
            $clientDef
        );

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias(
            $clientDef->getClass(),
            new Alias(sprintf(self::CONSUMER_NAME, $name), true)
        );
    }
}
