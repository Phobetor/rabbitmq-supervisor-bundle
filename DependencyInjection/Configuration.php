<?php

namespace Phobetor\RabbitMqSupervisorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This bundle uses the rabbit mq bundle's configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('rabbit_mq_supervisor');

        // Keep compatibility with symfony/config < 4.2
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('rabbit_mq_supervisor');
        }

        $rootNode
            ->children()
                ->scalarNode('worker_count')->defaultNull()->end()
                ->scalarNode('supervisor_instance_identifier')->defaultValue('symfony2')->end()
                ->scalarNode('sock_file_permissions')->defaultValue('0700')->end()
            ->end();
        $this->addPaths($rootNode);
        $this->addCommands($rootNode);
        $this->addConsumer($rootNode);

        return $treeBuilder;
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
                        ->scalarNode('workspace_directory')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/')->end()
                        ->scalarNode('configuration_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/supervisord.conf')->end()
                        ->scalarNode('pid_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/supervisor.pid')->end()
                        ->scalarNode('sock_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/supervisor.sock')->end()
                        ->scalarNode('log_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/supervisord.log')->end()
                        ->scalarNode('worker_configuration_directory')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/worker/')->end()
                        ->scalarNode('worker_output_log_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/logs/stdout.log')->end()
                        ->scalarNode('worker_error_log_file')->defaultValue('%kernel.project_dir%/supervisor/%kernel.environment%/logs/stderr.log')->end()
                        ->scalarNode('php_executable')->defaultValue('php')->end()
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
                        ->scalarNode('rabbitmq_batch_consumer')->defaultValue('rabbitmq:batch:consumer')->end()
                        ->scalarNode('rabbitmq_rpc_server')->defaultValue('rabbitmq:rpc-server')->end()
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
                ->addDefaultsIfNotSet()
                    ->children();

        $general = $consumerChildren
                        ->arrayNode('general');
        $this->addGeneralConsumerConfiguration($general);

        $individualPrototype = $consumerChildren
                        ->arrayNode('individual')
                            ->useAttributeAsKey('consumer')
                            ->prototype('array');
        $this->addIndividualConsumerConfiguration($individualPrototype);
    }

    /**
     * Add consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addGeneralConsumerConfiguration(ArrayNodeDefinition $node)
    {
        $node
        ->normalizeKeys(false)
        ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('messages')
                    ->min(0)
                    ->defaultValue(250)
                ->end()
                ->integerNode('memory-limit')
                    ->defaultNull()
                ->end()
                ->booleanNode('debug')
                    ->defaultNull()
                ->end()
                ->booleanNode('without-signals')
                    ->defaultNull()
                ->end()
                ->arrayNode('worker')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('count')
                            ->min(0)
                            ->defaultValue(1)
                        ->end()
                        ->integerNode('startsecs')
                            ->min(0)
                            ->defaultValue(2)
                        ->end()
                        ->integerNode('startretries')
                            ->min(1)
                            ->defaultValue(3)
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
                        ->scalarNode('user')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addIndividualConsumerConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->normalizeKeys(false)
            ->children()
                ->integerNode('messages')
                    ->min(0)
                    ->defaultNull()
                ->end()
                ->integerNode('memory-limit')
                    ->defaultNull()
                ->end()
                ->booleanNode('debug')
                    ->defaultNull()
                ->end()
                ->booleanNode('without-signals')
                    ->defaultNull()
                ->end()
                ->scalarNode('command')
                    ->defaultNull()
                ->end()
                ->arrayNode('worker')
                    ->children()
                        ->integerNode('count')
                            ->min(0)
                            ->defaultNull()
                        ->end()
                        ->integerNode('startsecs')
                            ->min(0)
                            ->defaultNull()
                        ->end()
                        ->integerNode('startretries')
                            ->min(1)
                            ->defaultValue(3)
                        ->end()
                        ->booleanNode('autorestart')
                            ->defaultNull()
                        ->end()
                        ->enumNode('stopsignal')
                            ->values(array('TERM', 'INT', 'KILL'))
                            ->defaultNull()
                        ->end()
                        ->booleanNode('stopasgroup')
                            ->defaultNull()
                        ->end()
                        ->integerNode('stopwaitsecs')
                            ->min(0)
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
