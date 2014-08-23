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

        $this->addSupervisor($rootNode);

        return $tree;
    }

    /**
     * Add supervisor conf to rabbit mq bundle's configuration
     * @param ArrayNodeDefinition $node
     */
    protected function addSupervisor(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('directory_workspace')->defaultValue('"%kernel.root_dir%/supervisor/%kernel.environment%')->end()
            ->end()
        ;
    }
}
