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
     * @var bool
     */
    private $waitForSupervisord = false;

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
     * @param bool $waitForSupervisord
     */
    public function setWaitForSupervisord($waitForSupervisord)
    {
        $this->waitForSupervisord = $waitForSupervisord;
    }

    /**
     * Execute a supervisorctl command
     *
     * @param $cmd string supervisorctl command
     * @param $failOnError bool indicate id errors should raise an exception
     * @return \Symfony\Component\Process\Process
     */
    public function execute($cmd, $failOnError = true)
    {
        $command = $this->createSupervisorControlCommand($cmd);
        $this->logger->debug('Executing: ' . $command);
        $p = $this->getProcess($command);
        $p->setWorkingDirectory($this->applicationDirectory);
        $p->run();
        if ($failOnError) {
            if ($p->getExitCode() !== 0) {
                $this->logger->critical(sprintf('supervisorctl returns code: %s', $p->getExitCodeText()));
            }
            $this->logger->debug('supervisorctl output: '. $p->getOutput());

            if ($p->getExitCode() !== 0) {
                throw new ProcessException($p);
            }
        }

        return $p;
    }

    /**
     * @param $cmd
     * @return string
     */
    private function createSupervisorControlCommand($cmd)
    {
        return sprintf(
            'supervisorctl%1$s %2$s',
            $this->configurationParameter,
            $cmd
        );
    }

    /**
     * Update configuration and processes
     */
    public function runAndReload()
    {

        // start supervisor and reload configuration
        $commands = [];
        $commands[] = sprintf(' && %s', $this->createSupervisorControlCommand('reread'));
        $commands[] = sprintf(' && %s', $this->createSupervisorControlCommand('update'));
        $this->run(implode('', $commands));
    }

    /**
     * Start supervisord if not already running
     *
     * @param $followingCommand string command to execute after supervisord was started
     */
    public function run($followingCommand = '')
    {
        $result = $this->execute('status', false)->getOutput();
        if (strpos($result, 'sock no such file') || strpos($result, 'refused connection')) {
            $command = sprintf(
                'supervisord%1$s%2$s%3$s',
                $this->configurationParameter,
                $this->identifierParameter,
                $followingCommand
            );
            $this->logger->debug('Executing: ' . $command);
            $p = $this->getProcess($command);
            $p->setWorkingDirectory($this->applicationDirectory);
            if (!$this->waitForSupervisord) {
                $p->start();
            } else {
                $p->run();
                if ($p->getExitCode() !== 0) {
                    $this->logger->critical(sprintf('supervisord returns code: %s', $p->getExitCodeText()));
                    throw new ProcessException($p);
                }
                $this->logger->debug('supervisord output: '. $p->getOutput());
            }
        }
    }

    /**
     * @param string $command
     * @return Process
     */
    private function getProcess($command)
    {
        // BC layer for Symfony 4.1 and older
        if (\method_exists(Process::class, 'fromShellCommandline')) {
            return Process::fromShellCommandline($command);
        }

        return new Process($command);
    }
}
