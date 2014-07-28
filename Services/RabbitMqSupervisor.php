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
        // generate program configuration files for all consumers
        foreach (array_keys($this->consumers) as $name) {
            $this->supervisor->genProgrammConf(
                $name,
                array(
                    'name' => $name,
                    'command' => sprintf('rabbitmq:consumer -m 250 %s', $name),
                    'numprocs' => 1,
                    'process_name' => '%(program_name)s_%(process_num)d',
                    'stopasgroup' => 'true',
                    'autorestart' => 'true',
                    'startsecs' => '2',
                    'stdout_logfile' => 'NONE',
                    'stderr_logfile' => 'NONE',

                )
            );
        }

        // update configuration
        $this->supervisor->run();
        $this->supervisor->reloadAndUpdate();

        // force restart
        $this->hup();
    }

    /**
     * Sent -HUP to supervisord to gracefully restart all processes
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
