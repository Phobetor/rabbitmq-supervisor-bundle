<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:generate')
            ->setDescription('rebuild supervisor worker configuration for all RabbitMQ consumer without (re)starting supervisord.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor $handler */
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');
        $handler->generate();
    }
}
