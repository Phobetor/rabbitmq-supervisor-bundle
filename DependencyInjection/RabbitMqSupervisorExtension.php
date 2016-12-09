<?php

namespace Phobetor\RabbitMqSupervisorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Fetch the RabbitMq bundle's consumer configuration.
 */
class RabbitMqSupervisorExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('phobetor_rabbitmq_supervisor.worker_count', $config['worker_count']);
        $container->setParameter('phobetor_rabbitmq_supervisor.supervisor_instance_identifier', $config['supervisor_instance_identifier']);
        $container->setParameter('phobetor_rabbitmq_supervisor.paths', $config['paths']);
        $container->setParameter('phobetor_rabbitmq_supervisor.workspace', $config['paths']['workspace_directory']);
        $container->setParameter('phobetor_rabbitmq_supervisor.configuration_file', $config['paths']['configuration_file']);
        $container->setParameter('phobetor_rabbitmq_supervisor.commands', $config['commands']);
        $container->setParameter('phobetor_rabbitmq_supervisor.worker_options', $config['worker_options']);
    }

    public function prepend(ContainerBuilder $container)
    {
        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'old_sound_rabbit_mq':
                    // take over this bundle's configuration
                    $extensionConfig = $container->getExtensionConfig($name);

                    foreach (array('consumers', 'multiple_consumers') as $attribute) {
                        if (isset($extensionConfig[0][$attribute])) {
                            $attributeValue = $extensionConfig[0][$attribute];
                        } else {
                            $attributeValue = array();
                        }
                        $container->setParameter('phobetor_rabbitmq_supervisor.' . $attribute, $attributeValue);
                    }
                    break;
            }
        }
    }

    public function getAlias()
    {
        return 'rabbit_mq_supervisor';
    }
}
