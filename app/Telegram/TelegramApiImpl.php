<?php

namespace App\Telegram;

class TelegramApiImpl implements TelegramApi
{
    const ENDPOINT = 'https://api.telegram.org/bot';
    private int $offset;
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getMessages(int $offset): array
    {
        $url = self::ENDPOINT . $this->token . '/getUpdates?timeout=1';
        $result = [];

        while (true) {
            $ch = curl_init("{$url}&offset={$offset}");

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = json_decode(curl_exec($ch), true);

            if (!is_array($response) || !isset($response['ok'])) {
                error_log("Invalid response: " . print_r($response, true));
                break;
            }

            foreach ($response['result'] as $data) {
                $chatId = $data['message']['chat']['id'];
                if (!isset($result[$chatId])) {
                    $result[$chatId] = [];
                }

                $result[$chatId][] = $data['message']['text'];

                $offset = $data['update_id'] + 1;
            }

            curl_close($ch);

            if (count($response['result']) < 100) break;
        }

        return [
            'offset' => $offset,
            'result' => $result,
        ];
    }


    public function sendMessages(int $chatId, string $text)
    {
        $url = self::ENDPOINT . $this->token . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        $ch = curl_init($url);

        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POST, true); // Specify the request method POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the encoded JSON data
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); // Set the content type to application/json
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response instead of printing it

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!$response['ok']) {
            echo "Telegram API error: " . $response['description'];
        }

        return $response;
    }
}
