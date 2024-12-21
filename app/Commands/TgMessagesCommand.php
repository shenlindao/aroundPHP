<?php

namespace App\Commands;

use App\Application;
use App\Telegram\TelegramApiImpl;

class TgMessagesCommand extends Command
{
    protected Application $app;
    protected TelegramApiImpl $tgApi;

    public function __construct(Application $app, TelegramApiImpl $tgApi)
    {
        $this->app = $app;
        $this->tgApi = $tgApi;
    }

    function run(array $options = []): void
    {
        $messages = $this->tgApi->getMessages(0);
        echo json_encode($this->tgApi->getMessages(0));

        foreach ($messages['result'] as $chatId => $texts) {
            foreach ($texts as $text) {
                if ($text == '/start') {
                    $this->tgApi->sendMessages($chatId, "Привет! Это бот.");
                } else {
                    $this->tgApi->sendMessages($chatId, "Вы отправили: $text");
                }
            }
        }
    }
}
