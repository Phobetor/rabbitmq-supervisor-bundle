<?php

namespace Phobetor\RabbitMqSupervisorBundle\Services;

use Ivan1986\SupervisorBundle\Service\Supervisor;

/**
 * @license MIT
 */
class RabbitMqSupervisor
{
    /**
     * @var \Ivan1986\SupervisorBundle\Service\Supervisor
     */
    private $supervisor;

    /**
     * @var string
     */
    private $appDirectory;

    /**
     * @var array
     */
    private $consumers;

    /**
     * Initialize Handler
     *
     * @param \Ivan1986\SupervisorBundle\Service\Supervisor $superisor
     * @param string $appDirectory
     * @param array $consumers
     *
     * @return \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor
     */
    public function __construct(Supervisor $superisor, $appDirectory, $consumers)
    {
        $this->supervisor = $superisor;
        $this->appDirectory = $appDirectory;
        $this->consumers = $consumers;
    }

    /**
     * Build supervisor configuration for all consumer daemons
     */
    public function build()
    {
        /** @var \SplFileInfo $item */
        foreach (new \DirectoryIterator(sprintf('%s/supervisor/', $this->appDirectory)) as $item) {
            if ($item->isDir()) {
                continue;
            }

            if ('conf' !== $item->getExtension()) {
                continue;
            }

            unlink($item->getRealPath());
        }

        // generate program configuration files for all consumers
        foreach (array_keys($this->consumers) as $name) {
            $this->supervisor->genProgrammConf(
                $name,
                array(
                    'name' => $name,
                    'command' => sprintf('rabbitmq:consumer -m %d %s', 250, $name),
                    'numprocs' => 1,
                    'options' => array(
                        'stopasgroup' => 'true',
                        'autorestart' => 'true',
                        'startsecs' => '2',
                        'stopwaitsecs' => '60',
                    )
                ),
                'RabbitMqSupervisorBundle:Supervisor:program.conf.twig'
            );
        }

        // update configuration
        $this->supervisor->run();
        $this->supervisor->reloadAndUpdate();

        // send SIGHUP to restart processes
        $this->hup();
    }

    /**
     * Send -HUP to supervisord to gracefully restart all processes
     */
    public function hup()
    {
        $pidPath = sprintf('%ssupervisord.pid', $this->appDirectory);
        if (is_file($pidPath) && is_readable($pidPath)) {
            $pid = (int)file_get_contents($pidPath);

            $command = sprintf('kill -HUP %d', $pid);
            `$command`;
        }
    }

    /**
     * Stop and start supervisord to force all processes to restart
     */
    public function restart()
    {
        $this->supervisor->execute('stop all');
        $this->supervisor->execute('start all');
    }
}
