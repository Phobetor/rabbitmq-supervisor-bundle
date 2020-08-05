<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControlCommand extends AbstractRabbitMqSupervisorAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:control')
            ->setDescription('Common commands to control the supervisord process')
            ->addArgument('cmd', InputArgument::REQUIRED, '(start|stop|restart|hup)')
            ->addOption('wait-for-supervisord', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rabbitMqSupervisor->setWaitForSupervisord((bool) $input->getOption('wait-for-supervisord'));

        switch ($input->getArgument('cmd')) {
            case 'start':
                $this->rabbitMqSupervisor->start();
                break;
            case 'stop':
                $this->rabbitMqSupervisor->stop();
                break;
            case 'restart':
                $this->rabbitMqSupervisor->restart();
                break;
            case 'hup':
                $this->rabbitMqSupervisor->hup();
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unknown command. Expected (start|stop|restart|hup), given "%s"',
                    $input->getArgument('cmd')
                ));
        }
        
        return 0;
    }
}
