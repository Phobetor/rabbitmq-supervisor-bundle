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

        foreach (array('consumers') as $attribute) {
            if (isset($config[$attribute])) {
                $container->setParameter('phobetor_rabbitmq_supervisor.' . $attribute, $config[$attribute]);
            }
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'old_sound_rabbit_mq':
                    // take over this bundle's configuration
                    $extensionConfig = $container->getExtensionConfig($name);
                    $container->prependExtensionConfig($this->getAlias(), $extensionConfig[0]);
                    break;
            }
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $this->processConfiguration(new Configuration(), $configs);
    }

    public function getAlias()
    {
        return 'rabbit_mq_supervisor';
    }
}
