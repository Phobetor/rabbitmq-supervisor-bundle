<?php

namespace Phobetor\RabbitMqSupervisorBundle\Services;

use Symfony\Component\Process\Process;

class Supervisor
{
    private $applicationDirectory;
    private $configurationParameter;
    private $identifierParameter;

    public function __construct($applicationDirectory, $configuration, $identifier)
    {
        $this->applicationDirectory = $applicationDirectory;
        $this->configurationParameter = $configuration ? (' --configuration=' . $configuration) : '';
        $this->identifierParameter    = $identifier    ? (' --identifier='    . $identifier)    : '';
    }

    /**
     * Execute a supervisorctl command
     *
     * @param $cmd string supervisorctl command
     * @return \Symfony\Component\Process\Process
     */
    public function execute($cmd)
    {
        $p = new Process(
            sprintf(
                'supervisorctl%1$s %2$s',
                $this->configurationParameter,
                $cmd
            )
        );
        $p->setWorkingDirectory($this->applicationDirectory);
        $p->run();
        return $p;
    }

    /**
     * Update configuration and processes
     */
    public function reloadAndUpdate()
    {
        $this->execute('reread');
        $this->execute('update');
    }

    /**
     * Start supervisord if not already running
     */
    public function run()
    {
        $result = $this->execute('status')->getOutput();
        if (strpos($result, 'sock no such file') || strpos($result, 'refused connection')) {
            $p = new Process(
                sprintf(
                    'supervisord%1$s%2$s',
                    $this->configurationParameter,
                    $this->identifierParameter
                )
            );
            $p->setWorkingDirectory($this->applicationDirectory);
            $p->start();
        }
    }
}
