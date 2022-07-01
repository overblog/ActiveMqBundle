<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\DependencyInjection;

use Overblog\ActiveMqBundle\ActiveMq\Connection;
use Overblog\ActiveMqBundle\ActiveMq\Consumer;
use Overblog\ActiveMqBundle\ActiveMq\Publisher;
use Overblog\ActiveMqBundle\Command\ConsumerCommand;
use Overblog\ActiveMqBundle\Command\ProducerCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OverblogActiveMqExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Register connections
        foreach ($config['connections'] as $name => $connection) {
            $this->addConnection($name, $connection, $container);
        }

        // Register publishers
        foreach ($config['publishers'] as $name => $producer) {
            $this->addPublisher($name, $producer, $container);
        }

        // Register consumers
        foreach ($config['consumers'] as $name => $consumer) {
            $this->addConsumer($name, $consumer, $container);
        }
    }

    private function addConnection($name, array $connection, ContainerBuilder $container): void
    {
        $definition = new Definition(Connection::class);
        $definition
            ->addArgument($connection)
            ->addTag('activemq.connection')
        ;

        $container->setDefinition(
            $this->buildConnectionServiceId($name),
            $definition
        );

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias(
            $definition->getClass(),
            new Alias($this->buildConnectionServiceId($name), true)
        );
    }

    private function addPublisher($name, array $publisher, ContainerBuilder $container): void
    {
        $definition = new Definition(Publisher::class);
        $definition
            ->addArgument(new Reference($this->buildConnectionServiceId($publisher['connection'])))
            ->addArgument($publisher['options'])
            ->addTag('activemq.publisher');
        $serviceID = sprintf('%s.publisher.%s', $this->getAlias(), $name);
        $container->setDefinition($serviceID, $definition);
        $container->getDefinition(ProducerCommand::class)
            ->addMethodCall('addPublisher', [$name, new Reference($serviceID)]);

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias($definition->getClass(), new Alias($serviceID, true));
    }

    private function addConsumer($name, array $consumer, ContainerBuilder $container)
    {
        $definition = new Definition(Consumer::class);
        $definition
            ->addArgument(new Reference($this->buildConnectionServiceId($consumer['connection'])))
            ->addArgument(new Reference($consumer['handler']))
            ->addArgument($consumer['options'])
            ->addTag('activemq.consumer');
        $serviceID = sprintf('%s.consumer.%s', $this->getAlias(), $name);
        $container->setDefinition($serviceID, $definition);
        $container->getDefinition(ConsumerCommand::class)
            ->addMethodCall('addConsumer', [$name, new Reference($serviceID)]);

        //@see https://github.com/overblog/ActiveMqBundle/issues/9
        $container->setAlias($definition->getClass(), new Alias($serviceID, true));

        return $definition;
    }

    private function buildConnectionServiceId($name)
    {
        return sprintf('%s.connection.%s', $this->getAlias(), $name);
    }
}
