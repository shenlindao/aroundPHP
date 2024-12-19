<?php

namespace App\Commands;

use App\Application;
use App\Telegram\TelegramApiImpl;

class TgMessagesCommand extends Command
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    function run(array $options  = []): void
    {     
        $tgApi = new TelegramApiImpl($this->app->env('TELEGRAM_TOKEN'));
        $messages = $tgApi->getMessages(0);
        echo json_encode($tgApi->getMessages(0));

        foreach ($messages['result'] as $chatId => $texts) {
            foreach ($texts as $text) {
                if ($text == '/start') {
                    $tgApi->sendMessages($chatId, "Привет! Это бот.");
                } else {
                    $tgApi->sendMessages($chatId, "Вы отправили: $text");
                }
            }
        }
    }
}