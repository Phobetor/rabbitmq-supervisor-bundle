<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:restart')
            ->setDescription('Stop and start supervisord to force all processes to restart.')
            ->addOption('wait-for-supervisord', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor $handler */
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');
        $handler->setWaitForSupervisord((bool) $input->getOption('wait-for-supervisord'));
        $handler->restart();
    }
}
