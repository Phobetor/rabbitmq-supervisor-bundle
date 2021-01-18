<?php

namespace Phobetor\RabbitMqSupervisorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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

        // check that commands do not contain sprintf specifiers that were required by older versions
        foreach ($config['commands'] as $command) {
            if (false !== strpos($command, '%')) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid configuration for path "%s": %s',
                    'rabbit_mq_supervisor.commands',
                    'command is no longer allowed to contain sprintf specifiers (e.g. "%1$d")'
                ));
            }
        }

        $consumer = [];
        if (!empty($config['consumer'])) {
            $consumer = $config['consumer'];
        }

        if (empty($consumer['general']['worker'])) {
            $consumer['general']['worker'] = [];
        }

        // take over worker count from old configuration key
        if (null !== $config['worker_count']) {
            $consumer['general']['worker']['count'] = $config['worker_count'];
        }

        // set default value of 250 if messages is not set or set to default value
        if (!array_key_exists('messages', $consumer['general']) || null === $consumer['general']['messages']) {
            $consumer['general']['messages'] = 250;
        }

        $container->setParameter('phobetor_rabbitmq_supervisor.config', array('consumer' => $consumer));
        $container->setParameter('phobetor_rabbitmq_supervisor.supervisor_instance_identifier', $config['supervisor_instance_identifier']);
        $container->setParameter('phobetor_rabbitmq_supervisor.sock_file_permissions', $config['sock_file_permissions']);
        $container->setParameter('phobetor_rabbitmq_supervisor.paths', $config['paths']);
        $container->setParameter('phobetor_rabbitmq_supervisor.inet_http_server', $config['inet_http_server']);
        $container->setParameter('phobetor_rabbitmq_supervisor.workspace', $config['paths']['workspace_directory']);
        $container->setParameter('phobetor_rabbitmq_supervisor.configuration_file', $config['paths']['configuration_file']);
        $container->setParameter('phobetor_rabbitmq_supervisor.commands', $config['commands']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $attributeNames = array('consumers', 'multiple_consumers', 'batch_consumers', 'rpc_servers');
        $attributes = array_combine($attributeNames, array_fill(0, count($attributeNames), []));

        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'old_sound_rabbit_mq':
                    // take over this bundle's configuration
                    $extensionConfig = $container->getExtensionConfig($name);

                    foreach ($attributeNames as $attribute) {
                        $attributeValue = array();

                        foreach ($extensionConfig as $config) {
                            if (isset($config[$attribute])) {
                                $attributeValue = array_merge($attributeValue, $config[$attribute]);
                            }
                        }
                        $attributes[$attribute] = $attributeValue;
                    }
                    break;
            }
        }

        foreach ($attributes as $name => $value) {
            $container->setParameter('phobetor_rabbitmq_supervisor.' . $name, $value);
        }
    }

    public function getAlias()
    {
        return 'rabbit_mq_supervisor';
    }
}
