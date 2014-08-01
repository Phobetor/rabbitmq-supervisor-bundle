<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:control')
            ->addArgument('command', InputArgument::REQUIRED, '(start|stop|restart|hup)')
            ->setDescription('Common commands to control the supervisord process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor $handler */
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');

        switch ($input->getArgument('command')) {
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
        }
    }
}
