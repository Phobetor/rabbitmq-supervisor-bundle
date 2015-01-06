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
                ->scalarNode('supervisor_instance_identifier')->defaultValue('symfony2')->end()
            ->end();
        $this->addPaths($rootNode);
        $this->addCommands($rootNode);

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
                        ->scalarNode('workspace_directory')             ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/')->end()
                        ->scalarNode('configuration_file')              ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisord.conf')->end()
                        ->scalarNode('pid_file')                        ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisor.pid')->end()
                        ->scalarNode('sock_file')                       ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisor.sock')->end()
                        ->scalarNode('log_file')                        ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/supervisord.log')->end()
                        ->scalarNode('worker_configuration_directory')  ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/worker/')->end()
                        ->scalarNode('worker_output_log_file')          ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/logs/stdout.log')->end()
                        ->scalarNode('worker_error_log_file')           ->defaultValue('%kernel.root_dir%/supervisor/%kernel.environment%/logs/stderr.log')->end()
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
                        ->scalarNode('rabbitmq_consumer')->defaultValue('rabbitmq:consumer -m %%1$d %%2$s')->end()
                        ->scalarNode('rabbitmq_multiple_consumer')->defaultValue('rabbitmq:multiple-consumer -m %%1$d %%2$s')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
