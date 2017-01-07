<?php

namespace Phobetor\RabbitMqSupervisorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This bundle uses the rabbit mq bundle's configuration
 */
class Configuration  implements ConfigurationInterface
{
    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();

        $rootNode = $tree->root('rabbit_mq_supervisor');

        $rootNode
            ->children()
                ->scalarNode('worker_count')->defaultNull()->end()
                ->scalarNode('supervisor_instance_identifier')->defaultValue('symfony2')->end()
            ->end();
        $this->addPaths($rootNode);
        $this->addCommands($rootNode);
        $this->addConsumer($rootNode);

        return $tree;
    }

    /**
     * Add paths configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addPaths(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('path')
            ->children()
                ->arrayNode('paths')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('workspace_directory')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/')->end()
                        ->scalarNode('configuration_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisord.conf')->end()
                        ->scalarNode('pid_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisor.pid')->end()
                        ->scalarNode('sock_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisor.sock')->end()
                        ->scalarNode('log_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisord.log')->end()
                        ->scalarNode('worker_configuration_directory')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/worker/')->end()
                        ->scalarNode('worker_output_log_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/logs/stdout.log')->end()
                        ->scalarNode('worker_error_log_file')->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/logs/stderr.log')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add commands configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addCommands(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('command')
            ->children()
                ->arrayNode('commands')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('rabbitmq_consumer')->defaultValue('rabbitmq:consumer')->end()
                        ->scalarNode('rabbitmq_multiple_consumer')->defaultValue('rabbitmq:multiple-consumer')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add general and individual consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addConsumer(ArrayNodeDefinition $node)
    {
        $consumerChildren = $node
            ->children()
                ->arrayNode('consumer')
                    ->children();

        $general = $consumerChildren
                        ->arrayNode('general');
        $this->addConsumerConfiguration($general);

        $individualPrototype = $consumerChildren
                        ->arrayNode('individual')
                            ->useAttributeAsKey('consumer')
                            ->prototype('array');
        $this->addConsumerConfiguration($individualPrototype);
    }

    /**
     * Add consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addConsumerConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->integerNode('messages')
                    ->defaultNull()
                ->end()
                ->integerNode('memory_limit')
                    ->defaultNull()
                ->end()
                ->booleanNode('debug')
                    ->defaultNull()
                ->end()
                ->booleanNode('without_signals')
                    ->defaultNull()
                ->end()
                ->arrayNode('worker')
                    ->children()
                        ->scalarNode('count')
                            ->defaultNull()
                        ->end()
                        ->integerNode('startsecs')
                            ->min(0)
                            ->defaultValue(2)
                        ->end()
                        ->booleanNode('autorestart')
                            ->defaultTrue()
                        ->end()
                        ->enumNode('stopsignal')
                            ->values(array('TERM', 'INT', 'KILL'))
                            ->defaultValue('INT')
                        ->end()
                        ->booleanNode('stopasgroup')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('stopwaitsecs')
                            ->min(0)
                            ->defaultValue(60)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
