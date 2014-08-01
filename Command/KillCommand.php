<?php

namespace Phobetor\RabbitMqSupervisorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KillCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq-supervisor:kill')
            ->addArgument('signal', InputArgument::REQUIRED, 'kill -signal')
            ->setDescription('Send the given signal via kill to supervisord.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Phobetor\RabbitMqSupervisorBundle\Services\RabbitMqSupervisor $handler */
        $handler = $this->getContainer()->get('phobetor_rabbitmq_supervisor');
        $handler->kill($input->getArgument('signal'));
    }
}
