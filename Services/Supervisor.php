<?php

namespace Phobetor\RabbitMqSupervisorBundle\Services;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Phobetor\RabbitMqSupervisorBundle\Exception\ProcessException;
use Symfony\Component\Process\Process;

class Supervisor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $applicationDirectory;

    /**
     * @var string
     */
    private $configurationParameter;

    /**
     * @var string
     */
    private $identifierParameter;

    /**
     * Supervisor constructor.
     *
     * @param string $applicationDirectory
     * @param string $configuration
     * @param string $identifier
     */
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
        $command = sprintf(
            'supervisorctl%1$s %2$s',
            $this->configurationParameter,
            $cmd
        );
        $this->logger->debug('Executing: ' . $command);
        $p = new Process($command);
        $p->setWorkingDirectory($this->applicationDirectory);
        $p->run();
        if ($p->getExitCode() !== 0) {
            $this->logger->critical(sprintf('supervisorctl returns code: %s', $p->getExitCodeText()));
        }
        $p->wait();
        $this->logger->debug('Output: '. $p->getOutput());

        if ($p->getExitCode() !== 0) {
            throw new ProcessException($p);
        }

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
            $command = sprintf(
                'supervisord%1$s%2$s',
                $this->configurationParameter,
                $this->identifierParameter
            );
            $this->logger->debug('Executing: ' . $command);
            $p = new Process($command);
            $p->setWorkingDirectory($this->applicationDirectory);
            $p->run();
            if ($p->getExitCode() !== 0) {
                $this->logger->critical(sprintf('supervisorctl returns code: %s', $p->getExitCodeText()));
                throw new ProcessException($p);
            }
        }
    }
}
