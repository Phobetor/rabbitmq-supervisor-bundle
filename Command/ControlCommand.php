<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControlCommand extends ContainerAwareCommand
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
        /** @var \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor $handler */
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');
        $handler->setWaitForSupervisord((bool) $input->getOption('wait-for-supervisord'));

        switch ($input->getArgument('cmd')) {
            case 'start':
                $handler->start();
                break;
            case 'stop':
                $handler->stop();
                break;
            case 'restart':
                $handler->restart();
                break;
            case 'hup':
                $handler->hup();
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unknown command. Expected (start|stop|restart|hup), given "%s"', $input->getArgument('cmd')
                ));
        }
    }
}
