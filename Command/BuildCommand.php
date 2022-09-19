<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends AbstractRabbitMqSupervisorAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:build')
            ->setDescription('Build supervisor worker configuration for all RabbitMQ consumer.')
            ->addOption('wait-for-supervisord', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rabbitMqSupervisor->setWaitForSupervisord((bool) $input->getOption('wait-for-supervisord'));
        $this->rabbitMqSupervisor->build();
        
        return 0;
    }
}
