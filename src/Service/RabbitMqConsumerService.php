<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RabbitMqConsumerService
{
    private const QUEUE_NAME = 'task_queue';

    private string $rabbitHost;
    private string $rabbitPort;
    private string $rabbitUser;
    private string $rabbitPassword;

    public function __construct(
        private AuditLogRepository    $auditLogRepository,
        private ParameterBagInterface $parameterBag,
    )
    {
        $this->rabbitHost = $this->parameterBag->get('rabbitmq.host');
        $this->rabbitPort = $this->parameterBag->get('rabbitmq.port');
        $this->rabbitUser = $this->parameterBag->get('rabbitmq.user');
        $this->rabbitPassword = $this->parameterBag->get('rabbitmq.password');
    }

    public function consume(OutputInterface $output): void
    {
        $connection = new AMQPStreamConnection(
            $this->rabbitHost,
            $this->rabbitPort,
            $this->rabbitUser,
            $this->rabbitPassword
        );

        $channel = $connection->channel();
        $channel->queue_declare(self::QUEUE_NAME, false, true, false, false);

        $output->writeln('<info>Waiting for messages...</info>');

        $callback = function (AMQPMessage $msg) use ($output) {
            $this->handleMessage($msg, $output);
        };

        $channel->basic_consume(self::QUEUE_NAME, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
    private function handleMessage(AMQPMessage $msg, OutputInterface $output): void
    {
        $data = json_decode($msg->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln('<error>Invalid JSON message received</error>');
            return;
        }

        try {
            $this->auditLogRepository->storeEvent($data['event'] ?? 'unknown', $data);
            $output->writeln('<info>Message stored</info>');
        } catch (\Throwable $e) {
            $output->writeln('<error>DB Error: ' . $e->getMessage() . '</error>');
        }
    }
}
