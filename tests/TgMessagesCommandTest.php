<?php

namespace App\Tests\Commands;

use App\Application;
use App\Commands\TgMessagesCommand;
use App\Telegram\TelegramApiImpl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Commands\TgMessagesCommand
 */
class TgMessagesCommandTest extends TestCase
{
    public function testRunHandlesMessagesCorrectly(): void
    {
        /** @var \App\Application|\PHPUnit\Framework\MockObject\MockObject $mockApplication */
        $mockApplication = $this->createMock(Application::class);
        $mockApplication->method('env')->willReturn('dummy-token');

        /** @var \App\Telegram\TelegramApiImpl|\PHPUnit\Framework\MockObject\MockObject $mockTelegramApi */
        $mockTelegramApi = $this->getMockBuilder(TelegramApiImpl::class)
            ->setConstructorArgs(['dummy-token'])
            ->onlyMethods(['getMessages', 'sendMessages'])
            ->getMock();

        $mockTelegramApi->method('getMessages')
            ->willReturn([
                'result' => [
                    12345 => ['/start', 'Привет!'],
                    67890 => ['Тест тест тест'],
                ],
            ]);

        $mockTelegramApi->expects($this->exactly(3))
            ->method('sendMessages')
            ->withConsecutive(
                [12345, "Привет! Это бот."],
                [12345, "Вы отправили: Привет!"],
                [67890, "Вы отправили: Тест тест тест"]
            );

        $command = new TgMessagesCommand($mockApplication, $mockTelegramApi);

        ob_start();
        $command->run();
        ob_end_clean();
    }
}
