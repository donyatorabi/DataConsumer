<?php

namespace App\Command;

use App\Service\RabbitMqConsumerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:consume-rabbitmq',
    description: 'Consume messages from RabbitMQ and store them in DB.',
)]
class ConsumeRabbitmqCommand extends Command
{
    public function __construct(private RabbitMqConsumerService $consumer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consumer->consume($output);
        return Command::SUCCESS;
    }
}
