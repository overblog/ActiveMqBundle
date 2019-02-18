<?php

namespace Overblog\ActiveMqBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const NAME = 'overblog_active_mq';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::NAME);
        $rootNode = self::getRootNodeWithoutDeprecation($treeBuilder, self::NAME);

        $rootNode
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('key')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('user')->defaultNull()->end()
                            ->scalarNode('password')->defaultNull()->end()
                            ->scalarNode('version')->defaultValue(1.1)->end()
                            ->scalarNode('randomize_failover')->defaultFalse()->end()
                            ->arrayNode('servers')
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('uri')->defaultValue('localhost:61613')->end()
                                        ->scalarNode('useAsyncSend')->defaultTrue()->end()
                                        ->scalarNode('startupMaxReconnectAttempts')->defaultNull()->end()
                                        ->scalarNode('maxReconnectAttempts')->defaultNull()->end()
                                        ->scalarNode('separator')->defaultValue('.')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('publishers')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->arrayNode('options')
                                ->children()
                                    ->scalarNode('type')->defaultValue('queue')->end()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('consumers')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->scalarNode('handler')->isRequired()->end()
                            ->arrayNode('options')
                                ->children()
                                    ->scalarNode('type')->defaultValue('queue')->end()
                                    ->scalarNode('name')->isRequired()->end()
                                    ->scalarNode('prefetchSize')->defaultValue(1)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            //Connection validation
            ->validate()
                ->ifTrue( function($v) {
                    foreach($v['publishers'] as $key => $producer)
                    {
                        if(!isset($v['connections'][$producer['connection']])) return true;
                    }

                    return false;
                })
                ->thenInvalid('Unknown connection in publishers configuration.')
            ->end()
            //Connection validation
            ->validate()
                ->ifTrue( function($v) {
                    foreach($v['consumers'] as $key => $producer)
                    {
                        if(!isset($v['connections'][$producer['connection']])) return true;
                    }

                    return false;
                })
                ->thenInvalid('Unknown connection in consumers configuration.')
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @param TreeBuilder $builder
     * @param string|null $name
     * @param string      $type
     *
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private static function getRootNodeWithoutDeprecation(TreeBuilder $builder, $name, $type = 'array')
    {
        // BC layer for symfony/config 4.1 and older
        return \method_exists($builder, 'getRootNode') ? $builder->getRootNode() : $builder->root($name, $type);
    }
}
