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
     * @param \Ivan1986\SupervisorBundle\Service\Supervisor $supervisor
     * @param string $appDirectory
     * @param array $consumers
     *
     * @return \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor
     */
    public function __construct(Supervisor $supervisor, $appDirectory, $consumers)
    {
        $this->supervisor = $supervisor;
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
        $this->kill('HUP');
    }

    /**
     * Send kill signal to supervisord
     *
     * @param string $signal
     * @param bool $waitForProcessToDisappear
     */
    public function kill($signal = '', $waitForProcessToDisappear = false)
    {
        $pid = $this->getSupervisorPid();
        if (!empty($pid) && $this->isProcessRunning($pid)) {
            if (!empty($signal)) {
                $signal = sprintf('-%s', $signal);
            }

            $command = sprintf('kill %s %d', $signal, $pid);

            `$command`;

            if ($waitForProcessToDisappear) {
                $this->wait();
            }
        }
    }

    /**
     * Wait for supervisord process to disappear
     */
    public function wait()
    {
        $pid = $this->getSupervisorPid();
        if (!empty($pid)) {
            while ($this->isProcessRunning($pid)) {
                sleep(1);
            }
        }
    }

    /**
     * Stop and start supervisord to force all processes to restart
     */
    public function restart()
    {
        $this->kill('', true);
        $this->supervisor->run();
    }

    /**
     * Check if a process with the given pid is running
     *
     * @param int $pid
     * @return bool
     */
    private function isProcessRunning($pid) {
        $state = array();
        exec(sprintf('ps %d', $pid), $state);

        /*
         * ps will return at least one row, the column labels.
         * If the process is running ps will return a second row with its status.
         */
        return 1 < count($state);
    }

    /**
     * Determines the supervisord process id
     *
     * @return null|int
     */
    private function getSupervisorPid() {
        $pidPath = sprintf('%slogs/supervisord.pid', $this->appDirectory);

        $pid = null;
        if (is_file($pidPath) && is_readable($pidPath)) {
            $pid = (int)file_get_contents($pidPath);
        }

        return $pid;
    }
}
