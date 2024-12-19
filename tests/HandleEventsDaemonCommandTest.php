<?php

use PHPUnit\Framework\TestCase;

class HandleEventsDaemonCommandTest extends TestCase
{
    public function testGetCurrentTime()
    {
        $handleEventsDaemonCommand = new \App\Commands\HandleEventsDaemonCommand(new \App\Application(dirname(path: __DIR__)));

        $result = $handleEventsDaemonCommand->getCurrentTime();

        self::assertNotEmpty($result);

        self::assertCount(5, $result);

        self::assertIsString($result[0]);

        self::assertIsString($result[1]);

        self::assertIsString($result[2]);

        self::assertIsString($result[3]);

        self::assertIsString($result[4]);

        self::assertGreaterThanOrEqual(0, (int)$result[0]);
        self::assertLessThanOrEqual(59, (int)$result[0]);

        self::assertGreaterThanOrEqual(0, (int)$result[1]);
        self::assertLessThanOrEqual(23, (int)$result[1]);

        self::assertGreaterThanOrEqual(1, (int)$result[2]);
        self::assertLessThanOrEqual(31, (int)$result[2]);

        self::assertGreaterThanOrEqual(1, (int)$result[3]);
        self::assertLessThanOrEqual(12, (int)$result[3]);

        self::assertGreaterThanOrEqual(0, (int)$result[4]);
        self::assertLessThanOrEqual(6, (int)$result[4]);

        self::assertEquals(
            [
                date("i"),
                date("H"),
                date("d"),
                date("m"),
                date("w")
            ],
            $result
        );
    }
}
