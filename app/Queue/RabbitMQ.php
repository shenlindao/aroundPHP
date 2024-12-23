<?php

namespace App\Queue;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class RabbitMQ implements Queue
{

    private AMQPMessage|null $lastMessage;
    private AbstractChannel | AMQPChannel $channel;
    private AMQPStreamConnection $connection;

    public function __construct(private string $queueName)
    {
        $this->lastMessage = null;
    }

    public function sendMessage($message): void
    {
        try {
            $this->open();

            $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $this->channel->basic_publish($msg, '', $this->queueName);

            echo "Сообщение отправлено в очередь '{$this->queueName}': {$message}\n";
        } catch (AMQPRuntimeException $e) {
            echo "Ошибка при отправке сообщения: {$e->getMessage()}\n";
        } finally {
            $this->close();
        }
    }

    public function getMessage(): ?string
    {
        try {
            $this->open();

            $msg = $this->channel->basic_get($this->queueName);

            if ($msg) {
                $this->lastMessage = $msg;
                return $msg->getBody();
            }

            echo "Сообщение не найдено в очереди '{$this->queueName}'.\n";
            return null;
        } catch (AMQPRuntimeException $e) {
            echo "Ошибка при получении сообщения: {$e->getMessage()}\n";
            return null;
        } finally {
            $this->close();
        }
    }

    public function ackLastMessage(): void
    {
        $this->lastMessage?->ack();

        $this->close();
    }

    private function open()
    {
        echo "Подключение к RabbitMQ...\n";

        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();

        echo "Объявляем очередь '{$this->queueName}'...\n";
        $this->channel->queue_declare($this->queueName, true, false, false, false);

        echo "Подключение и очередь успешно настроены.\n";
    }

    private function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}
