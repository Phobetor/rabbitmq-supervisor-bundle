<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends AbstractRabbitMqSupervisorAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:init')
            ->setDescription('Build supervisor configuration.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rabbitMqSupervisor->init();
        
        return 0;
    }
}
