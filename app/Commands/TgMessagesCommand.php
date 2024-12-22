<?php

namespace App\Commands;

use App\Application;
use App\Cache\Redis;
use App\Telegram\TelegramApiImpl;
use Predis\Client;

class TgMessagesCommand extends Command
{
    protected Application $app;
    private int $offset;
    private ?array $oldMessages;
    private Redis $redis;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->offset = 0;
        $this->oldMessages = null;

        $client = new Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);

        $this->redis = new Redis($client);
    }

    public function run(array $options = []): void
    {
        $messages = $this->receiveNewMessages();
        echo json_encode($messages);

        // foreach ($messages as $chatId => $texts) {
        //     foreach ($texts as $text) {
        //         if ($text === '/start') {
        //             $this->tgApi->sendMessages($chatId, "Привет! Это бот.");
        //         } else {
        //             $this->tgApi->sendMessages($chatId, "Вы отправили: $text");
        //         }
        //     }
        // }
    }

    private function getTelegramApiImpl(): TelegramApiImpl
    {
        return new TelegramApiImpl($this->app->env('TELEGRAM_TOKEN'));
    }

    private function receiveNewMessages(): array
    {
        $this->offset = (int)$this->redis->get('tg_messages:offset') ?? 0;

        $result = $this->getTelegramApiImpl()->getMessages($this->offset);

        $this->redis->set('tg_messages:offset', $result['offset'] ?? $this->offset);

        $oldMessagesJson = $this->redis->get('tg_messages:old_messages');
        $this->oldMessages = $oldMessagesJson ? json_decode($oldMessagesJson, true) : [];

        $messages = [];

        foreach ($result['result'] ?? [] as $chatId => $newMessage) {
            if (isset($this->oldMessages[$chatId])) {
                $this->oldMessages[$chatId] = array_merge($this->oldMessages[$chatId], $newMessage);
            } else {
                $this->oldMessages[$chatId] = $newMessage;
            }

            $messages[$chatId] = $this->oldMessages[$chatId];
        }

        $this->redis->set('tg_messages:old_messages', json_encode($this->oldMessages));
        return $messages;
    }
}
